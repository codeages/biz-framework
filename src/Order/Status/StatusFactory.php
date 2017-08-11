<?php

namespace Codeages\Biz\Framework\Order\Status;

class StatusFactory
{
    static $factory;
    private $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function getStatusProcessor($status)
    {
        return $this->biz["order_status.{$status}"];
    }
}