<?php

namespace Tests\Cache;

use Codeages\Biz\Framework\Cache\NamespacedCacheManager;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

class ExampleUserServiceImpl
{
    /**
     * @var Biz
     */
    private $biz;

    private $users = [
        ['id' => 1, 'username' => 'user_1', 'lastLoginIp' => '192.168.1.1', ],
        ['id' => 2, 'username' => 'user_2', 'lastLoginIp' => '192.168.1.2', ],
        ['id' => 3, 'username' => 'user_3', 'lastLoginIp' => '192.168.1.3', ],
    ];

    /**
     * @var NamespacedCacheManager
     */
    private $cache;

    /**
     * @param Biz $biz
     */
    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
        $this->cache = new NamespacedCacheManager($biz['cache'], 'user');
    }

    public function getById($id)
    {
        foreach ($this->users as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }
        return null;
    }

    public function getByUsername($username)
    {
        foreach ($this->users as $user) {
            if ($user['username'] == $username) {
                return $user;
            }
        }
        return null;
    }

    public function getByIdCached($id)
    {
        return $this->cache->getById($id, function () use ($id) {
            $user = $this->getById($id);
            return array_filter_keys($user, [ 'id', 'username',]);
        });
    }

    public function getByUsernameCached($username)
    {
        return $this->cache->getByRef("username_$username", function () use ($username) {
            $user = $this->getByUsername($username);
            return array_filter_keys($user, [ 'id', 'username',]);
        });
    }

    public function register($user)
    {
        // 注册属于低频业务，这里我们不使用缓存，直接从数据库总捞取
        $existUser = $this->getByUsername($user['username']);
        if ($existUser) {
            throw new ServiceException("Register failed, username exist. (username: {$user['username']}");
        }

        // ....省略了一些注册业务代码....

        $registered = [
            'id' => 4,
            'username' => $user['username'],
            'lastLoginIp' => '192.168.1.4',
        ];

        $this->users[] = $registered;

        $this->cache->del([
            // 因为 CacheManager会缓存 null 值，所以我们这里新用户的 id 也需要清除缓存
            'id' => [$registered['id'],],
            'key' => [
                // 因为 CacheManager 会缓存 null 值，所以我们这里新用户的 username 也需要清除缓存
                "username_{$registered['username']}",
                // 最近的登录用户的缓存可删可不删，看实际业务情况
                // 本示例中，我们对该key设置了300秒的缓存时长
                // 如果业务上可以容忍300秒内，界面上不更新最新登录用户，那么可以不删除，等缓存自动过期
                // 通常建议不要删除此类缓存，因为用户感知不明显，还是性能优先
                'top10LatestLoginUsers',
            ]
        ]);

        return $registered;
    }

    /**
     * 增长用户的在线时长
     *
     * 这里我们假设前端会每隔 1 分钟心跳请求，每一次心跳请求，我们就需要增加 60 秒 在线时长，
     * 那么这个请求是比较频繁的，也会造成大量的数据库更新
     * 对于在线时长这种容许存在误差的数据字段，我们并不需要实时每次去更新数据库，我们可以先缓存起来，以降低数据库写入的压力
     *
     * @param $id
     * @param $time
     * @return void
     */
    public function increaseOnlineTime($id, $time)
    {
        // @todo
    }

    public function changeUsername($id, $newUsername)
    {
        // 变更用户名，应属于低频业务，这里就没必要用cache，我们直接从数据库获取用户对象
        $user = $this->getById($id);

        if (empty($user)) {
            throw new ServiceException("user not found (id: $id).");
        }

        foreach ($this->users as &$user) {
            if ($user['id'] == $id) {
                $user['username'] = $newUsername;
            }
        }

        $this->cache->del([
            'id' => $id,
            'key' => [
                // 删除老的用户名的Cache
                "username_{$user['username']}",
                // 因为 CacheManager 会缓存 null 值，所以我们这里新用户名的缓存也需要清除缓存
                "username_${newUsername}",
            ],
        ]);
    }

    /**
     * 获取最新登录的 10 个用户
     *
     * @return mixed
     */
    public function getTop10LatestLoginUsers()
    {
        return $this->cache->get('top10LatestLoginUsers', function () {
            $users = $this->users;
            return array_walk_transform($users, function ($user) {
                return array_filter_keys($user, [ 'id', 'username',]);
            });
        }, ['ttl' => 300]);
    }

}