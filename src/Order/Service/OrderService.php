<?php

namespace Codeages\Biz\Framework\Order\Service;

interface OrderService
{
    public function createOrder($order, $orderItems);

    public function setOrderPaid($data);

    public function closeOrder($id);

    public function closeOrders();

    public function findOrderItemsByOrderId($orderId);

    public function findOrderItemDeductsByItemId($itemId);

    public function finishOrder($id);

    public function finishOrders();

    public function signSuccessOrder($id, $data);

    public function signFailOrder($id, $data);

    public function applyItemRefund($id, $data);

    public function applyRefund($orderId, $data);

    public function finishRefund($id, $data);

    public function refuseRefund($id, $data);

    public function getOrder($id);

    public function getOrderBySn($sn, $lock = false);

    public function searchOrders($conditions, $orderBy, $start, $limit);

    public function countOrders($conditions);

    public function searchOrderItems($conditions, $orderBy, $start, $limit);

    public function countOrderItems($conditions);

    public function findOrdersByIds(array $ids);
}