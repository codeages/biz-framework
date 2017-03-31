<?php
namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy;

class DoubleCacheStrategy extends AbstractCacheStrategy implements CacheStrategy
{
    private $first;

    private $second;

    public function setStrategies($first, $second)
    {
        $this->first = $first;
        $this->second = $second;

    }

    public function beforeGet($table, $method, $arguments)
    {
        $cache = $this->first->beforeGet($table, $method, $arguments);
        if ($cache) {
            return $cache;
        }

        return $this->second->beforeGet($table, $method, $arguments);
    }

    public function afterGet($table, $method, $arguments, $row)
    {
        $this->first->afterGet($table, $method, $arguments, $row);
        $this->second->afterGet($table, $method, $arguments, $row);
    }

    public function beforeFind($table, $methd, $arguments)
    {
        $cache = $this->first->beforeFind($table, $method, $arguments);
        if ($cache) {
            return $cache;
        }

        return $this->second->beforeFind($table, $method, $arguments);
    }

    public function afterFind($table, $methd, $arguments, array $rows)
    {
        $this->first->afterGet($method, $arguments, $rows);
        $this->second->afterGet($method, $arguments, $rows);
    }

    public function beforeSearch($table, $methd, $arguments)
    {
        $cache = $this->first->beforeSearch($method, $arguments);
        if ($cache) {
            return $cache;
        }

        return $this->second->beforeSearch($method, $arguments);
    }

    public function afterSearch($table, $methd, $arguments, array $rows)
    {
        $this->first->afterSearch($method, $arguments, $rows);
        $this->second->afterSearch($method, $arguments, $rows);
    }

    public function afterCreate($table, $methd, $arguments, $row)
    {
        $this->first->afterCreate($method, $arguments, $row);
        $this->second->afterCreate($method, $arguments, $row);
    }

    public function afterUpdate($table, $methd, $arguments, $row)
    {
        $this->first->afterUpdate($method, $arguments, $row);
        $this->second->afterUpdate($method, $arguments, $row);
    }

    public function afterDelete($table, $methd, $arguments)
    {
        $this->first->afterDelete($method, $arguments);
        $this->second->afterDelete($method, $arguments);
    }
}