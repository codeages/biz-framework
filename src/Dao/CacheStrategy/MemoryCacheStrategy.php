<?php
namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy;

class MemoryCacheStrategy implements CacheStrategy
{
    protected $cache = array();

    public function beforeGet($method, $arguments)
    {
        $key = $this->key($method, $arguments);
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        return null;
    }

    public function afterGet($method, $arguments, $row)
    {
        $key = $this->key($method, $arguments);
        $this->cache[$key] = $row;
    }

    public function beforeFind($methd, $arguments)
    {
        return null;
    }

    public function afterFind($methd, $arguments, array $rows)
    {

    }

    public function beforeSearch($methd, $arguments)
    {
        return null;
    }

    public function afterSearch($methd, $arguments, array $rows)
    {

    }

    public function afterCreate($methd, $arguments, $row)
    {

    }

    public function afterUpdate($methd, $arguments, $row)
    {
        $this->cache = array();
    }

    public function afterDelete($methd, $arguments)
    {
        $this->cache = array();
    }

    protected function key($methd, $arguments)
    {

    }
}