<?php
namespace Codeages\Biz\Framework\Dao;

class CacheEvent
{
    public $key;

    public $value;

    public function __construct($key, $value = null)
    {
        $this->key = $key;
        $this->value = $value;
    }
}