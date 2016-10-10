<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

class TableCacheStrategy extends CacheStrategy
{
    public function get($rootNameSpace, $args)
    {
        return $this->fetchCache($rootNameSpace, $args);
    }

    public function find($rootNameSpace, $args)
    {
        return $this->fetchCache($rootNameSpace, $args);
    }

    public function create($rootNameSpace, $args)
    {
        return $this->deleteCache($rootNameSpace, $args);
    }

    public function update($rootNameSpace, $args)
    {
        return $this->deleteCache($rootNameSpace, $args);
    }

    public function delete($rootNameSpace, $args)
    {
        return $this->deleteCache($rootNameSpace, $args);
    }

    public function wave($rootNameSpace, $args)
    {
        return $this->deleteCache($rootNameSpace, $args);
    }

    protected function deleteCache($rootNameSpace, $args)
    {
        $callback = array_pop($args);
        $data     = call_user_func_array($callback, $args);
        $this->incrNamespaceVersion($rootNameSpace);
        return $data;
    }
}
