<?php

namespace Codeages\Biz\Framework\Dao\DaoProxy;

use Codeages\Biz\Framework\Dao\DaoException;
use Codeages\Biz\Framework\Dao\SerializerInterface;
use Codeages\Biz\Framework\Dao\DaoInterface;

class DaoProxy
{
    /**
     * @var DaoInterface
     */
    protected $dao;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct($container, DaoInterface $dao, SerializerInterface $serializer)
    {
        $this->container = $container;
        $this->dao = $dao;
        $this->serializer = $serializer;
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
        foreach (array('get', 'find', 'search', 'create', 'update', 'wave', 'delete') as $prefix) {
            if (strpos($method, $prefix) === 0) {
                return $prefix;
            }
        }
        return null;
    }

    protected function wave($method, $arguments)
    {
        $result = $this->callRealDao($method, $arguments);
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
        $this->unserialize($row);

        return $row;
    }

    protected function create($method, $arguments)
    {
        $declares = $this->dao->declares();
        if (isset($declares['timestamps'][0])) {
            $arguments[0][$declares['timestamps'][0]] = time();
        }

        if (isset($declares['timestamps'][1])) {
            $arguments[0][$declares['timestamps'][1]] = time();
        }

        $this->serialize($arguments[0]);
        $row = $this->callRealDao($method, $arguments);
        $this->unserialize($row);

        return $row;
    }

    protected function delete($method, $arguments)
    {
        $result = $this->callRealDao($method, $arguments);
        return $result;
    }

    protected function get($method, $arguments)
    {
        $row = $this->callRealDao($method, $arguments);
        $this->unserialize($row);
        return $row;
    }

    protected function find($method, $arguments)
    {
        $rows = $this->callRealDao($method, $arguments);
        $this->unserializes($rows);
        return $rows;
    }

    protected function search($method, $arguments)
    {
        $rows = $this->callRealDao($method, $arguments);
        $this->unserializes($rows);

        return $rows;
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

    private function getCacheStrategy()
    {
        if (empty($this->container['dao.cache.enabled'])) {
            return null;
        }

        $declares = $this->dao->declares();
        if (empty($declares['cache'])) {
            return null;
        }

        $key = 'cache.dao.strategy.'.$declares['cache'];
        if (!isset($this->container[$key])) {
            throw new DaoException("Dao cache strategy `{$key}` is not defined.");
        }

        if (empty($this->container['dao.cache.double.enabled'])) {
            return $this->container[$key];
        }

        $double = $this->container['dao.cache.double'];
        $double->setStrategies($this->container['dao.cache.double.first'], $this->container[$key]);

        return $double;
    }
}
