<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

class TableCacheStrategy extends CacheStrategy
{
    public function fetchCache($rootNameSpace, $args, $callback)
    {
        $orginArgs = $args;
        array_shift($args);
        $method = array_shift($args);
        $key    = $this->generateKey($rootNameSpace, $method, $args[0]);
        $data   = $this->_getCacheCluster()->get($key);

        if ($data !== false) {
            return $data;
        }

        $data = call_user_func_array($callback, $orginArgs);

        $this->_getCacheCluster()->setex($key, self::MAX_LIFE_TIME, $data);

        return $data;
    }

    public function deleteCache($rootNameSpace, $args)
    {
        $this->incrNamespaceVersion($rootNameSpace);
    }
}
