<?php

namespace Codeages\Biz\Framework\Validation;

interface Validator
{
    public function validate($data, $rules, $throwException = true);

    public function rule($name, $callback, $message = null);

    public function errors();
}
