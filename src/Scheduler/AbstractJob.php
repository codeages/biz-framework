<?php

namespace Codeages\Biz\Framework\Scheduler;

class AbstractJob implements Job, \ArrayAccess
{
    private $params = array();

    public function __construct($params = array())
    {
        $this->params = $params;
    }

    public function execute()
    {

    }

    public function __get($name)
    {
        return empty($this->params[$name] ) ? '' : $this->params[$name];
    }

    function __set($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->params[] = $value;
        } else {
            $this->params[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->params[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->params[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->params[$offset]) ? $this->params[$offset] : null;
    }
}