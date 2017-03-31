<?php
namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy;

/**
 * 表级别缓存策略
 */
class TabelCacheStrategy extends AbstractCacheStrategy implements CacheStrategy
{
    const LIFE_TIME = 3600;

    public function beforeGet($table, $method, $arguments)
    {
        $key = $this->key($method, $arguments);
        return $this->redis->get($key);
    }

    public function afterGet($table, $method, $arguments, $row)
    {
        $key = $this->key($method, $arguments);
        return $this->redis->set($key, $row, self::LIFE_TIME);
    }

    public function beforeFind($table, $method, $arguments)
    {
        $key = $this->key($method, $arguments);
        return $this->redis->get($key);
    }

    public function afterFind($table, $method, $arguments, array $rows)
    {
        $key = $this->key($method, $arguments);
        return $this->redis->set($key, $rows, self::LIFE_TIME);
    }

    public function beforeSearch($table, $method, $arguments)
    {
        $key = $this->key($method, $arguments);
        return $this->redis->get($key);
    }

    public function afterSearch($table, $method, $arguments, array $rows)
    {
        $key = $this->key($method, $arguments);
        return $this->redis->set($key, $rows, self::LIFE_TIME);
    }

    public function afterCreate($table, $method, $arguments, $row)
    {
        $this->upTableVersion($table);
    }

    public function afterUpdate($table, $method, $arguments, $row)
    {
        $this->upTableVersion($table);
    }

    public function afterDelete($table, $method, $arguments)
    {
        $this->upTableVersion($table);
    }

    private function getTableVersion($table)
    {
        $key = "dao:{$table}:v";
        $version = $this->redis->get($key);
        if ($version === false) {
            return $this->redis->incr($key);
        }

        return $version;
    }

    private function upTableVersion($table)
    {
        $key = "dao:{$table}:v";
        return $this->redis->incr($key);
    }

    private function key($table, $method, $arguments)
    {
        $version = $this->getTableVersion();
        $key = sprintf("dao:%s:v:%s:%s:%s", $table, $version, $method, json_encode($arguments));

        return $key;
    }
}