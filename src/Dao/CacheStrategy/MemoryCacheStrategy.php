<?php
namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy;

/**
 * 内存缓存策略
 */
class MemoryCacheStrategy extends AbstractCacheStrategy implements CacheStrategy
{
    protected $cache = array();

    public function beforeGet($table, $method, $arguments)
    {
        $key = $this->key($method, $arguments);
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        return false;
    }

    public function afterGet($table, $method, $arguments, $row)
    {
        $key = $this->key($method, $arguments);
        $this->cache[$key] = $row;
    }

    public function beforeFind($table, $methd, $arguments)
    {
        return false;
    }

    public function afterFind($table, $methd, $arguments, array $rows)
    {

    }

    public function beforeSearch($table, $methd, $arguments)
    {
        return false;
    }

    public function afterSearch($table, $methd, $arguments, array $rows)
    {

    }

    public function afterCreate($table, $methd, $arguments, $row)
    {

    }

    public function afterUpdate($table, $methd, $arguments, $row)
    {
        $this->cache = array();
    }

    public function afterDelete($table, $methd, $arguments)
    {
        $this->cache = array();
    }
}
