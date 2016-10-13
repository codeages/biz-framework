<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

class TableCacheStrategy extends CacheStrategy
{
    public function set($daoMethod, $arguments, $data)
    {
        $prefix = $this->getPrefix($daoMethod, array('get', 'find'));

        if (!empty($prefix)) {
            $key = $this->generateKey($daoMethod, $arguments);
            $this->_getCacheCluster()->setex($key, self::MAX_LIFE_TIME, $data);
        }
    }

    public function get($daoMethod, $arguments)
    {
        $prefix = $this->getPrefix($daoMethod, array('get', 'find'));

        if (!empty($prefix)) {
            $key = $this->generateKey($daoMethod, $arguments);
            return $this->_getCacheCluster()->get($key);
        }
    }

    public function wave($daoProxyMethod, $daoMethod, $arguments, $callback)
    {
        $data = call_user_func_array($callback, array($daoProxyMethod, $daoMethod, $arguments));
        $this->incrNamespaceVersion($this->rootNameSpace);
        return $data;
    }

    protected function generateKey($method, $args)
    {
        if ($method == 'get') {
            return "{$this->rootNameSpace}:{$this->getVersionByNamespace($this->rootNameSpace)}:id:{$args[0]}";
        }

        $fileds = $this->parseFileds($method);
        $keys   = '';
        foreach ($fileds as $key => $value) {
            if (!empty($keys)) {
                $keys = "{$keys}:";
            }
            $keys = $keys.$value.':'.$args[$key];
        }

        return "{$this->rootNameSpace}:{$this->getVersionByNamespace($this->rootNameSpace)}:{$keys}";
    }
}
