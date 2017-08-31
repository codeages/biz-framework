<?php

namespace Codeages\Biz\Framework\Order\Service;

interface OrderItemRefundService
{
    public function searchRefundItems($conditions, $orderby, $start, $limit);

    public function countRefundItems($conditions);
}