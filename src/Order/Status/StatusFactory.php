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

    public static function instance($biz)
    {
        if(empty(self::$factory)) {
            self::$factory = new StatusFactory($biz);
        }
        return self::$factory;
    }

    public function getStatusProcessor($status)
    {
        return $this->biz["order_status.{$status}"];
    }
}