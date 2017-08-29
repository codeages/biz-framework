<?php

namespace Codeages\Biz\Framework\Order;

interface AbstractPaidProcessor
{
    const SUCCESS = 'success';

    public function paidCallback($order);
}