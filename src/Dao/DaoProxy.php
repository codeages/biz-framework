<?php

namespace Codeages\Biz\Framework\Dao;

class DaoProxy
{
    protected $container;

    protected $dao;

    public function __construct($container, $dao)
    {
        $this->container = $container;
        $this->dao = $dao;
    }

    public function __call($method, $arguments)
    {
        if (strpos($method, 'get') === 0) {
            $row = $this->callRealDao($method, $arguments);

            return $this->unserialize($row);
        }

        if ((strpos($method, 'find') === 0) || (strpos($method, 'search') === 0)) {
            $rows = $this->callRealDao($method, $arguments);

            return $this->unserializes($rows);
        }

        $declares = $this->dao->declares();
        if (strpos($method, 'create') === 0) {
            if (isset($declares['timestamps'][0])) {
                $arguments[0][$declares['timestamps'][0]] = time();
            }

            if (isset($declares['timestamps'][1])) {
                $arguments[0][$declares['timestamps'][1]] = time();
            }

            $arguments[0] = $this->serialize($arguments[0]);
            $row = $this->callRealDao($method, $arguments);

            return $this->unserialize($row);
        }

        if (strpos($method, 'update') === 0) {
            if (isset($declares['timestamps'][1])) {
                $arguments[1][$declares['timestamps'][1]] = time();
            }
            $arguments[1] = $this->serialize($arguments[1]);

            $row = $this->callRealDao($method, $arguments);

            return $this->unserialize($row);
        }

        return $this->callRealDao($method, $arguments);
    }

    private function callRealDao($method, $arguments)
    {
        return call_user_func_array(array($this->dao, $method), $arguments);
    }

    private function unserialize(&$row)
    {
        if (empty($row)) {
            return $row;
        }

        $declares = $this->dao->declares();
        $serializes = empty($declares['serializes']) ? array() : $declares['serializes'];

        foreach ($serializes as $key => $method) {
            if (!isset($row[$key])) {
                continue;
            }
            $method = "{$method}Unserialize";
            $row[$key] = $this->$method($row[$key]);
        }

        return $row;
    }

    private function unserializes(array &$rows)
    {
        if (empty($rows)) {
            return $rows;
        }

        foreach ($rows as &$row) {
            $this->unserialize($row);
        }

        return $rows;
    }

    private function serialize(&$row)
    {
        $declares = $this->dao->declares();
        $serializes = empty($declares['serializes']) ? array() : $declares['serializes'];

        foreach ($serializes as $key => $method) {
            if (!isset($row[$key])) {
                continue;
            }
            $method = "{$method}Serialize";
            $row[$key] = $this->$method($row[$key]);
        }

        return $row;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function jsonSerialize($value)
    {
        if (empty($value)) {
            return '';
        }

        return json_encode($value);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function jsonUnserialize($value)
    {
        if (empty($value)) {
            return array();
        }

        return json_decode($value, true);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function delimiterSerialize($value)
    {
        if (empty($value)) {
            return '';
        }

        return '|'.implode('|', $value).'|';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function delimiterUnserialize($value)
    {
        if (empty($value)) {
            return array();
        }

        return explode('|', trim($value, '|'));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function phpSerialize($value)
    {
        return serialize($value);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function phpUnserialize($value)
    {
        return unserialize($value);
    }
}
