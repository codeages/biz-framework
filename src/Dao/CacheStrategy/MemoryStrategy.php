<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

/**
 * 内存缓存策略.
 */
class MemoryStrategy implements CacheStrategy
{
    protected $cache = array();

    public function beforeQuery(GeneralDaoInterface $dao, $method, $arguments)
    {
        if (strpos($method, 'get') !== 0) {
            return false;
        }

        $key = $this->key($dao, $method, $arguments);
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        return false;
    }

    public function afterQuery(GeneralDaoInterface $dao, $method, $arguments, $data)
    {
        if (strpos($method, 'get') !== 0) {
            return;
        }

        $key = $this->key($dao, $method, $arguments);
        $this->cache[$key] = $data;
    }

    public function afterCreate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->cache = array();
    }

    public function afterUpdate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->cache = array();
    }

    public function afterWave(GeneralDaoInterface $dao, $method, $arguments, $affected)
    {
        $this->cache = array();
    }

    public function afterDelete(GeneralDaoInterface $dao, $method, $arguments)
    {
        $this->cache = array();
    }

    private function key(GeneralDaoInterface $dao, $method, $arguments)
    {
        $key = sprintf('dao:%s:%s:%s', $dao->table(), $method, json_encode($arguments));

        return $key;
    }
}
