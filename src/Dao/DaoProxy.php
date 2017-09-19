<?php

namespace Codeages\Biz\Framework\Dao;

use Codeages\Biz\Framework\Dao\Annotation\MetadataReader;

class DaoProxy
{
    /**
     * @var GeneralDaoInterface
     */
    protected $dao;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var CacheStrategy
     */
    protected $cacheStrategy;

    /**
     * @var ArrayStorage
     */
    protected $arrayStorage;

    /**
     * @var MetadataReader
     */
    protected $metadataReader;

    public function __construct($container, DaoInterface $dao, MetadataReader $metadataReader, SerializerInterface $serializer, ArrayStorage $arrayStorage = null)
    {
        $this->container = $container;
        $this->dao = $dao;
        $this->metadataReader = $metadataReader;
        $this->serializer = $serializer;
        $this->arrayStorage = $arrayStorage;
    }

    public function __call($method, $arguments)
    {
        $proxyMethod = $this->getProxyMethod($method);
        if ($proxyMethod) {
            return $this->$proxyMethod($method, $arguments);
        } else {
            return $this->callRealDao($method, $arguments);
        }
    }

    protected function getProxyMethod($method)
    {
        foreach (array('get', 'find', 'search', 'count', 'create', 'batchCreate', 'batchUpdate', 'batchDelete', 'update', 'wave', 'delete') as $prefix) {
            if (strpos($method, $prefix) === 0) {
                return $prefix;
            }
        }

        return null;
    }

    protected function get($method, $arguments)
    {
        $lastArgument = end($arguments);
        reset($arguments);

        // lock模式下，因为需要借助mysql的锁，不走cache
        if (is_array($lastArgument) && isset($lastArgument['lock']) && $lastArgument['lock'] === true) {
            $row = $this->callRealDao($method, $arguments);
            $this->unserialize($row);

            return $row;
        }

        if ($this->arrayStorage) {
            $key = $this->getCacheKey($this->dao, $method, $arguments);
            if (!empty($this->arrayStorage[$key])) {
                return $this->arrayStorage[$key];
            }
        }

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $cache = $strategy->beforeQuery($this->dao, $method, $arguments);
            if ($cache !== false) {
                return $cache;
            }
        }

        $row = $this->callRealDao($method, $arguments);
        $this->unserialize($row);
        $this->arrayStorage && ($this->arrayStorage[$this->getCacheKey($this->dao, $method, $arguments)] = $row);

        if ($strategy) {
            $strategy->afterQuery($this->dao, $method, $arguments, $row);
        }

        return $row;
    }

    protected function find($method, $arguments)
    {
        return $this->search($method, $arguments);
    }

    protected function search($method, $arguments)
    {
        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $cache = $strategy->beforeQuery($this->dao, $method, $arguments);
            if ($cache !== false) {
                return $cache;
            }
        }

        $rows = $this->callRealDao($method, $arguments);
        $this->unserializes($rows);

        if ($strategy) {
            $strategy->afterQuery($this->dao, $method, $arguments, $rows);
        }

        return $rows;
    }

    protected function count($method, $arguments)
    {
        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $cache = $strategy->beforeQuery($this->dao, $method, $arguments);
            if ($cache !== false) {
                return $cache;
            }
        }

        $count = $this->callRealDao($method, $arguments);

        if ($strategy) {
            $strategy->afterQuery($this->dao, $method, $arguments, $count);
        }

        return $count;
    }

    protected function create($method, $arguments)
    {
        $declares = $this->dao->declares();

        $time = time();

        if (isset($declares['timestamps'][0])) {
            $arguments[0][$declares['timestamps'][0]] = $time;
        }

        if (isset($declares['timestamps'][1])) {
            $arguments[0][$declares['timestamps'][1]] = $time;
        }

        $this->serialize($arguments[0]);
        $row = $this->callRealDao($method, $arguments);
        $this->unserialize($row);

        $this->arrayStorage && $this->arrayStorage->flush();

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $this->buildCacheStrategy()->afterCreate($this->dao, $method, $arguments, $row);
        }

        return $row;
    }

    protected function batchCreate($method, $arguments)
    {
        $declares = $this->dao->declares();

        end($arguments);
        $lastKey = key($arguments);
        reset($arguments);

        if (!is_array($arguments[$lastKey])) {
            throw new DaoException('batchCreate method arguments last element must be array type');
        }

        $time = time();
        $rows = $arguments[$lastKey];

        foreach ($rows as &$row) {
            if (isset($declares['timestamps'][0])) {
                $row[$declares['timestamps'][0]] = $time;
            }

            if (isset($declares['timestamps'][1])) {
                $row[$declares['timestamps'][1]] = $time;
            }

            $this->serialize($row);
            unset($row);
        }

        $arguments[$lastKey] = $rows;

        $result = $this->callRealDao($method, $arguments);

        $this->flushTableCache();

        return $result;
    }

    protected function batchUpdate($method, $arguments)
    {
        $declares = $this->dao->declares();

        $time = time();
        $rows = $arguments[1];

        foreach ($rows as &$row) {
            if (isset($declares['timestamps'][1])) {
                $row[$declares['timestamps'][1]] = $time;
            }

            $this->serialize($row);
        }

        $arguments[1] = $rows;

        $result = $this->callRealDao($method, $arguments);

        $this->flushTableCache();

        return $result;
    }

    protected function batchDelete($method, $arguments)
    {
        $result = $this->callRealDao($method, $arguments);

        $this->flushTableCache();

        return $result;
    }

    protected function wave($method, $arguments)
    {
        $result = $this->callRealDao($method, $arguments);

        $this->arrayStorage && $this->arrayStorage->flush();

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $this->buildCacheStrategy()->afterWave($this->dao, $method, $arguments, $result);
        }

        return $result;
    }

    protected function update($method, $arguments)
    {
        $declares = $this->dao->declares();

        end($arguments);
        $lastKey = key($arguments);
        reset($arguments);

        if (!is_array($arguments[$lastKey])) {
            throw new DaoException('update method arguments last element must be array type');
        }

        if (isset($declares['timestamps'][1])) {
            $arguments[$lastKey][$declares['timestamps'][1]] = time();
        }

        $this->serialize($arguments[$lastKey]);

        $row = $this->callRealDao($method, $arguments);

        if (is_array($row)) {
            $this->unserialize($row);
        }

        if (!is_array($row) && !is_numeric($row) && !is_null($row)) {
            throw new DaoException('update method return value must be array type or int type');
        }

        $this->arrayStorage && $this->arrayStorage->flush();

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $this->buildCacheStrategy()->afterUpdate($this->dao, $method, $arguments, $row);
        }

        return $row;
    }

    protected function delete($method, $arguments)
    {
        $result = $this->callRealDao($method, $arguments);

        $this->arrayStorage && $this->arrayStorage->flush();

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $this->buildCacheStrategy()->afterDelete($this->dao, $method, $arguments);
        }

        return $result;
    }

    protected function callRealDao($method, $arguments)
    {
        return call_user_func_array(array($this->dao, $method), $arguments);
    }

    protected function unserialize(&$row)
    {
        if (empty($row)) {
            return;
        }

        $declares = $this->dao->declares();
        $serializes = empty($declares['serializes']) ? array() : $declares['serializes'];

        foreach ($serializes as $key => $method) {
            if (!array_key_exists($key, $row)) {
                continue;
            }

            $row[$key] = $this->serializer->unserialize($method, $row[$key]);
        }
    }

    protected function unserializes(array &$rows)
    {
        foreach ($rows as &$row) {
            $this->unserialize($row);
        }
    }

    protected function serialize(&$row)
    {
        $declares = $this->dao->declares();
        $serializes = empty($declares['serializes']) ? array() : $declares['serializes'];

        foreach ($serializes as $key => $method) {
            if (!array_key_exists($key, $row)) {
                continue;
            }

            $row[$key] = $this->serializer->serialize($method, $row[$key]);
        }
    }

    private function flushTableCache()
    {
        $this->arrayStorage && ($this->arrayStorage->flush());

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $this->buildCacheStrategy()->flush($this->dao);
        }
    }

    /**
     * @return CacheStrategy|null
     */
    private function buildCacheStrategy()
    {
        if (!empty($this->cacheStrategy)) {
            return $this->cacheStrategy;
        }

        if (empty($this->container['dao.cache.enabled'])) {
            return null;
        }

        if (!empty($this->container['dao.cache.annotation'])) {
            $strategy = $this->getStrategyFromAnnotation($this->dao);
            if ($strategy) {
                return $strategy;
            }
        }

        $declares = $this->dao->declares();
        if (isset($declares['cache']) && $declares['cache'] === false) {
            return null;
        }

        if (!empty($declares['cache'])) {
            return $this->container['dao.cache.strategy.'.$declares['cache']];
        }

        if (isset($this->container['dao.cache.strategy.default'])) {
            return $this->container['dao.cache.strategy.default'];
        }

        return null;
    }

    private function getStrategyFromAnnotation($dao)
    {
        $metadata = $this->metadataReader->read($dao);
        if (empty($metadata)) {
            return null;
        }

        return $this->container['dao.cache.strategy.'.strtolower($metadata['strategy'])];
    }

    private function getCacheKey(GeneralDaoInterface $dao, $method, $arguments)
    {
        $key = sprintf('dao:%s:%s:%s', $dao->table(), $method, json_encode($arguments));

        return $key;
    }
}
