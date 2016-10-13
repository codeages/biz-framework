<?php

namespace Codeages\Biz\Framework\Dao;

use Codeages\Biz\Framework\Dao\CacheStrategy\CacheStrategyFactory;

class CacheDelegate
{
    private $strategy; // table, promise

    public function __construct($dao, $cacheConfig)
    {
        $this->strategy = CacheStrategyFactory::getCacheStrategy($dao, $cacheConfig);
    }

    public function proccess($daoProxyMethod, $daoMethod, $arguments, $callback)
    {
        $prefix = $this->getPrefix($daoMethod, array('get', 'find', 'create', 'update', 'delete', 'wave'));
        if (empty($prefix)) {
            throw new \InvalidArgumentException('args is invalid. ');
        }

        if (in_array($prefix, array('get', 'find'))) {
            return $this->fetchCache($daoProxyMethod, $daoMethod, $arguments, $callback);
        } else {
            return $this->strategy->wave($daoProxyMethod, $daoMethod, $arguments, $callback);
        }
    }

    protected function fetchCache($daoProxyMethod, $daoMethod, $arguments, $callback)
    {
        $data = $this->strategy->get($daoMethod, $arguments);

        if ($data !== false) {
            return $data;
        }

        $data = call_user_func_array($callback, array($daoProxyMethod, $daoMethod, $arguments));

        $this->strategy->set($daoMethod, $arguments, $data);

        return $data;
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
}
