<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Redis\RedisClusterFactory;

abstract class CacheStrategy
{
    protected $config;
    const MAX_LIFE_TIME = 86400;

    abstract public function get($rootNameSpace, $args);
    abstract public function find($rootNameSpace, $args);
    abstract public function create($rootNameSpace, $args);
    abstract public function update($rootNameSpace, $args);
    abstract public function delete($rootNameSpace, $args);
    abstract public function wave($rootNameSpace, $args);

    public function __construct($config)
    {
        $this->config = $config;
    }

    protected function parseFileds($method)
    {
        $prefixs = array('get', 'find');
        $prefix  = $this->getPrefix($method, $prefixs);

        if (empty($prefix)) {
            return array();
        }

        $method = str_replace($prefix.'By', '', $method);

        $fileds = explode("And", $method);
        foreach ($fileds as $key => $filed) {
            $fileds[$key] = lcfirst($filed);
        }

        return $fileds;
    }

    protected function getPrefix($str, $prefixs)
    {
        $_prefix = '';
        foreach ($prefixs as $prefix) {
            if (strpos($str, $prefix) === 0) {
                $_prefix = $prefix;
                break;
            }
        }

        return $_prefix;
    }

    protected function fetchCache($rootNameSpace, $args)
    {
        $callback       = array_pop($args);
        $orginArgs      = $args;
        $proxyDaoMethod = array_shift($args);
        $method         = array_shift($args);
        $key            = $this->generateKey($rootNameSpace, $method, $args[0]);

        $data = $this->_getCacheCluster()->get($key);

        if ($data !== false) {
            return $data;
        }

        $data = call_user_func_array($callback, $orginArgs);

        $this->_getCacheCluster()->setex($key, self::MAX_LIFE_TIME, $data);

        return $data;
    }

    protected function generateKey($rootNameSpace, $method, $args)
    {
        if ($method == 'get') {
            return "{$rootNameSpace}:{$this->getVersionByNamespace($rootNameSpace)}:id:{$args[0]}";
        }

        $fileds = $this->parseFileds($method);
        $keys   = '';
        foreach ($fileds as $key => $value) {
            if (!empty($keys)) {
                $keys = "{$keys}:";
            }
            $keys = $keys.$value.':'.$args[$key];
        }

        return "{$rootNameSpace}:{$this->getVersionByNamespace($rootNameSpace)}:{$keys}";
    }

    protected function incrNamespaceVersion($namespace)
    {
        $this->_getCacheCluster()->incr("version:{$namespace}");
    }

    protected function getVersionByNamespace($namespace)
    {
        return $this->_getCacheCluster()->get("version:{$namespace}");
    }

    protected function _getCacheCluster()
    {
        return RedisClusterFactory::instance($this->config)->getCluster();
    }
}
