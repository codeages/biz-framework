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
        $this->eventDispatcher->dispatch(new CacheEvent($key, $value, $lifetime), 'dao.cache.set');
    }

    public function incr($key)
    {
        $newValue = $this->redis->incr($key);
        $this->eventDispatcher->dispatch(new CacheEvent($key, $newValue), 'dao.cache.set');
    }

    public function del($key)
    {
        $this->redis->del($key);
        $this->eventDispatcher->dispatch(new CacheEvent($key), 'dao.cache.del');
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->redis, $name], $arguments);
    }
}
