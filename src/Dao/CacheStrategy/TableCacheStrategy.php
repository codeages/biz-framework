<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

class TableCacheStrategy extends CacheStrategy
{
    public function wave($dao, $method, $arguments, $callback)
    {
        $data = call_user_func_array($callback, array($method, $arguments));
        $rootNameSpace = $dao->table();
        $this->incrNamespaceVersion($rootNameSpace);
        return $data;
    }

    protected function generateKey($dao, $method, $args)
    {   
        $rootNameSpace = $dao->table();
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
}
