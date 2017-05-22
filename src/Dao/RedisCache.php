<?php
namespace Codeages\Biz\Framework\Dao;

use Symfony\Component\EventDispatcher\EventDispatcher;

class RedisCache
{
    /**
     * @var \Redis|\RedisArray
     */
    protected $redis;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    public function __construct($redis, EventDispatcher $eventDispatcher)
    {
        $this->redis = $redis;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function set($key, $value, $lifetime = 0)
    {
        $this->redis->set($key, $value, $lifetime);
        $this->eventDispatcher->dispatch('dao.cache.set');
    }

    public function del($key)
    {
        $this->redis->del($key);
        $this->eventDispatcher->dispatch('dao.cache.del');
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->redis, $name), $arguments);
    }
}