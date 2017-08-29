<?php

namespace Codeages\Biz\Framework\Order;

interface PaidCallback
{
    const SUCCESS = 'success';

    public function paidCallback($order);
}