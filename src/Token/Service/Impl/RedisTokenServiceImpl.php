<?php
namespace Codeages\Biz\Framework\Token\Service\Impl;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Token\Dao\TokenDao;
use Codeages\Biz\Framework\Token\Service\TokenService;
use Codeages\Biz\Framework\Service\BaseService;

class RedisTokenServiceImpl extends BaseService implements TokenService
{
    /**
     * @var \Redis|\RedisArray
     */
    protected $redis;

    public function __construct(Biz $biz)
    {
        parent::__construct($biz);
        $this->redis = $this->biz['redis'];
    }

    public function generate($place, $lifetime, $times = 0, $data = null)
    {
        $token = array();
        $token['place'] = $place;
        $token['key'] = $this->_makeTokenValue(32);
        $token['data'] = $data;
        $token['expired_time'] = empty($lifetime) ? 0 : time() + $lifetime;
        $token['times'] = $times;
        $token['remaining_times'] = $times;
        $token['created_time'] = time();

        if ($lifetime) {
            $this->redis->set($this->key($place, $token['key']), $token, $lifetime);
        } else {
            $this->redis->set($this->key($place, $token['key']), $token);
        }

        return $token;
    }

    public function verify($place, $key)
    {
        $key = $this->key($place, $key);
        $token = $this->redis->get($key);

        if (empty($token)) {
            return false;
        }

        if ($token['times'] > 0 && ($token['remaining_times'] < 1)) {
            $this->redis->del($key);
            return false;
        }

        if ($token['remaining_times'] >= 1) {
            $token['remaining_times'] = $token['remaining_times'] - 1;
            if ($token['expired_time'] > 0) {
                $ttl = $token['expired_time'] - time();
                if ($ttl <= 0) {
                    $this->redis->del($key);
                }
            } else {
                $ttl = 0;
            }

            if ($ttl > 0) {
                $this->redis->set($key, $token, $ttl);
            } else {
                $this->redis->set($key, $token);
            }
        }

        if ($token['times'] > 0 && $token['remaining_times'] == 0) {
            $this->redis->del($key);
        }

        return $token;
    }

    public function destroy($place, $key)
    {
        return $this->redis->del($this->key($place, $key));
    }

    protected function _makeTokenValue($length)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $value = '';

        for ($i = 0; $i < $length; ++$i) {
            $value .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $value;
    }

    protected function key($place, $key)
    {
        return "biz:token:{$place}:{$key}";
    }

    /**
     * @return TokenDao
     */
    protected function getTokenDao()
    {
        return $this->biz->dao('Token:TokenDao');
    }
}
