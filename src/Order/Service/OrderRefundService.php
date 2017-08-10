<?php

namespace Codeages\Biz\Framework\Order\Service;

interface OrderRefundService
{
    public function applyItemRefund($id, $data);

    public function applyRefund($orderId, $data);

    public function applyOrderItemsRefund($orderId, $orderItemIds, $data);

    public function finishRefund($id);

    public function adoptRefund($id, $data);

    public function refuseRefund($id, $data);

}