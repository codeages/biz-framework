<?php

namespace Codeages\Biz\Framework\Validation;

class ValidationException extends \InvalidArgumentException
{
    protected $key;
    protected $value;
    protected $message;

    public function __construct($key, $value, $message)
    {
        parent::__construct('The given data failed to pass validation.');
        $this->key = $key;
        $this->value = $value;
        $this->message = $message;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }
}