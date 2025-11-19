<?php

namespace Codeages\Biz\Framework\Dao;

use ReturnTypeWillChange;

class ArrayStorage implements \ArrayAccess
{
    private $data = [];

    public function flush()
    {
        $this->data = [];
    }

    #[ReturnTypeWillChange] public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    #[ReturnTypeWillChange] public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    #[ReturnTypeWillChange] public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    #[ReturnTypeWillChange] public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }
}
