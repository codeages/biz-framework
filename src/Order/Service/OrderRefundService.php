<?php

namespace Codeages\Biz\Framework\Order\Service;

interface OrderRefundService
{
    public function applyOrderItemRefund($id, $data);

    public function applyOrderRefund($orderId, $data);

    public function applyOrderItemsRefund($orderId, $orderItemIds, $data);

    public function setRefunded($id, $data = array());

    public function setRefunding($id, $data = array());

    public function setRefused($id, $data = array());

}