<?php

namespace Codeages\Biz\Framework\Redis;

use Redis;

class RedisClusterFactory
{
    private $config;

    private $pool;

    private static $instance;

    private function __construct($config)
    {
        $this->config = $config;
    }

    public static function instance($config)
    {
        if (!self::$instance) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    private function isSingle($config)
    {
        return empty($config['servers']);
    }

    public function getCluster($group = 'default')
    {
        $poolKey = "{$group}:master";

        if (isset($this->pool[$poolKey])) {
            return $this->pool[$poolKey];
        }

        if (!isset($this->config[$group])) {
            throw new \InvalidArgumentException("Group '{$group}' is not exist.");
        }

        $cnf = $this->config[$group];

        if ($this->isSingle($cnf)) {
            $redis = new Redis();
            $redis->pconnect($cnf['host'], $cnf['port'], $cnf['timeout'], $cnf['reserved'], $cnf['retry_interval']);
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        } else {
            $redis = new MultipleRedis($cnf);
        }

        $this->pool[$poolKey] = $redis;

        return $redis;
    }
}
