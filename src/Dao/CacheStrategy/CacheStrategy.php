<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Redis\RedisClusterFactory;

abstract class CacheStrategy
{
    protected $config;
    protected $dao;
    protected $rootNameSpace;
    const MAX_LIFE_TIME = 86400;

    abstract public function set($daoMethod, $arguments, $data);
    abstract public function get($daoMethod, $arguments);
    abstract public function wave($daoProxyMethod, $daoMethod, $arguments, $callback);

    public function __construct($dao, $config)
    {
        $this->config        = $config;
        $this->dao           = $dao;
        $this->rootNameSpace = $dao->table();
    }

    protected function parseFileds($daoMethod)
    {
        $prefixs = array('get', 'find');
        $prefix  = $this->getPrefix($daoMethod, $prefixs);

        if (empty($prefix)) {
            return array();
        }

        $daoMethod = str_replace($prefix.'By', '', $daoMethod);

        $fileds = explode("And", $daoMethod);
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
