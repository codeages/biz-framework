<?php

namespace Codeages\Biz\Framework\Dao;

interface FieldSerializer
{
    public function serialize($method, $value);

    public function unserialize($method, $value);
}
