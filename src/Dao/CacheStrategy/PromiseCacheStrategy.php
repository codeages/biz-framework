<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

class PromiseCacheStrategy extends CacheStrategy
{
    private $methodMap = array();
    private $fieldsMap = array();

    public function wave($dao, $daoMethod, $arguments, $callback)
    {
        $rootNameSpace = $dao->table();
        $className = get_class($dao);
        if (in_array($daoMethod, array('update', 'delete'))) {
            $originData = $dao->get($arguments[0]);
            $data       = call_user_func_array($callback, array($daoMethod, $arguments));
            if(empty($this->methodMap[$className])) {
                return;
            }

            foreach ($this->methodMap[$className] as $method => $fields) {
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
                    $this->incrNamespaceVersion("{$rootNameSpace}:{$keys}");
                }
            }
        } else {
            $data = call_user_func_array($callback, array($daoMethod, $arguments));
            $this->incrNamespaceVersion($rootNameSpace);
        }
        return $data;
    }

    public function parseDao($dao)
    {
        $className = get_class($dao);
        $class   = new \ReflectionClass($className);
        $methods = $class->getMethods();
        foreach ($methods as $key => $method) {
            if ($method->isPublic()) {
                $methodName = $method->getName();
                $whiteList  = array('__construct', 'declares', 'db', 'table');
                if (in_array($methodName, $whiteList)) {
                    continue;
                }
                $this->parseMethod($className, $methodName);
            }
        }
    }

    protected function parseMethod($className, $methodName)
    {
        if ($methodName == 'get') {
            $this->push('id', $methodName, $className);
        }

        $prefix = $this->getPrefix($methodName, array('get', 'find'));
        if ($prefix && $prefix != $methodName) {
            $truncateMethodName = str_replace("{$prefix}By", '', $methodName);
            $fields             = explode('And', $truncateMethodName);

            if (!isset($this->methodMap[$className])) {
                $this->methodMap[$methodName] = array();
            }

            if(empty($this->methodMap[$className][$methodName])){
                $this->methodMap[$className][$methodName] = $fields;
            }

            foreach ($fields as $field) {
                $this->push($field, $methodName, $className);
            }
        }
    }

    protected function push($field, $methodName, $className)
    {
        $field = strtolower($field);
        if (!isset($this->fieldsMap[$className])) {
            $this->fieldsMap[$methodName] = array();
        }

        if (empty($this->fieldsMap[$methodName][$field])) {
            $this->fieldsMap[$methodName][$field] = array($methodName);
        } else {
            array_push($this->fieldsMap[$methodName][$field], $methodName);
        }
    }

    
    protected function generateKey($dao, $method, $args)
    {
        $rootNameSpace = $dao->table();
        if ($method == 'get') {

            return "{$rootNameSpace}:{$this->getVersionByNamespace($rootNameSpace)}:id:{$args[0]}";
        }

        $keys    = $this->getKeys($method, $args);
        $version = $this->getVersionByNamespace("{$rootNameSpace}:{$keys}");

        return "{$rootNameSpace}:version:{$this->getVersionByNamespace($rootNameSpace)}:{$keys}:version:{$version}";
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
