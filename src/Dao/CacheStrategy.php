<?php

namespace Codeages\Biz\Framework\Dao;

interface CacheStrategy
{
    public function call($method, $arguments);
}