<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

class PromiseCacheStrategy extends CacheStrategy
{
    protected $fieldsMap;
    protected $methodMap;
    protected $dao;

    public function __construct($dao, $config)
    {
        parent::__construct($dao, $config);
        $this->parseFieldsMap($dao);
        $this->dao = $dao;
    }

    protected function parseFieldsMap($dao)
    {
        $class   = new \ReflectionClass(get_class($dao));
        $methods = $class->getMethods();
        foreach ($methods as $key => $method) {
            if ($method->isPublic()) {
                $methodName = $method->getName();
                $whiteList  = array('__construct', 'declares', 'db', 'table');
                if (in_array($methodName, $whiteList)) {
                    continue;
                }
                $this->parseMethod($methodName);
            }
        }
    }

    protected function parseMethod($methodName)
    {
        if ($methodName == 'get') {
            $this->push('id', $methodName);
        }

        $prefix = $this->getPrefix($methodName, array('get', 'find'));
        if ($prefix && $prefix != $methodName) {
            $truncateMethodName = str_replace("{$prefix}By", '', $methodName);
            $fields             = explode('And', $truncateMethodName);

            if (empty($this->methodMap[$methodName])) {
                $this->methodMap[$methodName] = $fields;
            }

            foreach ($fields as $field) {
                $this->push($field, $methodName);
            }
        }
    }

    protected function push($field, $methodName)
    {
        $field = strtolower($field);
        if (empty($this->fieldsMap[$field])) {
            $this->fieldsMap[$field] = array($methodName);
        } else {
            array_push($this->fieldsMap[$field], $methodName);
        }
    }

    public function set($daoMethod, $arguments, $data)
    {
        $prefix = $this->getPrefix($daoMethod, array('get', 'find'));

        if (!empty($prefix)) {
            $key = $this->generateKey($daoMethod, $arguments);
            $this->_getCacheCluster()->setex($key, self::MAX_LIFE_TIME, $data);
        }
    }

    public function get($daoMethod, $arguments)
    {
        $prefix = $this->getPrefix($daoMethod, array('get', 'find'));

        if (!empty($prefix)) {
            $key = $this->generateKey($daoMethod, $arguments);
            return $this->_getCacheCluster()->get($key);
        }
    }

    public function wave($daoProxyMethod, $daoMethod, $arguments, $callback)
    {
        if (in_array($daoMethod, array('update', 'delete'))) {
            $originData = $this->dao->get($arguments[0]);
            $data       = call_user_func_array($callback, array($daoProxyMethod, $daoMethod, $arguments));
            foreach ($this->methodMap as $method => $fields) {
                $shouldIncrNamespace = false;
                foreach ($fields as $key => $field) {
                    $field = lcfirst($field);
                    if ($originData[$field] != $data[$field]) {
                        $shouldIncrNamespace = true;
                    }
                }

                if ($shouldIncrNamespace) {
                    $args = array();
                    foreach ($fields as $fieldKey) {
                        $fieldKey = lcfirst($fieldKey);
                        $args[]   = $originData[$fieldKey];
                    }

                    $keys = $this->getKeys($method, $args);
                    $this->incrNamespaceVersion("{$this->rootNameSpace}:{$keys}");
                }
            }
        } else {
            $data = call_user_func_array($callback, array($daoProxyMethod, $daoMethod, $arguments));
            $this->incrNamespaceVersion($this->rootNameSpace);
        }
        return $data;
    }

    protected function generateKey($method, $args)
    {
        if ($method == 'get') {
            return "{$this->rootNameSpace}:{$this->getVersionByNamespace($this->rootNameSpace)}:id:{$args[0]}";
        }

        $keys    = $this->getKeys($method, $args);
        $version = $this->getVersionByNamespace("{$this->rootNameSpace}:{$keys}");

        return "{$this->rootNameSpace}:version:{$this->getVersionByNamespace($this->rootNameSpace)}:{$keys}:version:{$version}";
    }

    protected function getKeys($method, $args)
    {
        $fileds = $this->parseFileds($method);
        $keys   = '';
        foreach ($fileds as $key => $value) {
            if (!empty($keys)) {
                $keys = "{$keys}:";
            }
            $keys = $keys.$value.':'.$args[$key];
        }

        return $keys;
    }
}
