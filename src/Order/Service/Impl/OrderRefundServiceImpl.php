<?php

namespace Codeages\Biz\Framework\Order\Service\Impl;

use Codeages\Biz\Framework\Order\Service\OrderRefundService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

class OrderRefundServiceImpl extends BaseService implements OrderRefundService
{
    public function applyItemRefund($id, $data)
    {
        $this->validateLogin();
        $orderItem = $this->getOrderItemDao()->get($id);
        if (empty($orderItem)) {
            throw $this->createNotFoundException("order_item #{$id} is not found");
        }

        $order = $this->getOrderDao()->get($orderItem['order_id']);
        if (empty($order)) {
            throw $this->createNotFoundException("order #{$orderItem['order_id']} is not found");
        }

        if ($order['status'] != 'signed') {
            throw $this->createAccessDeniedException("order #${$order['id']} status is not signed.");
        }

        if ($this->biz['user']['id'] != $order['user_id']) {
            throw $this->createAccessDeniedException("order #{$orderItem['order_id']} can not refund.");
        }

        $orderRefund = $this->getOrderRefundDao()->create(array(
            'order_id' => $orderItem['order_id'],
            'order_item_id' => $orderItem['id'],
            'sn' => $this->generateSn(),
            'user_id' => $order['user_id'],
            'created_user_id' => $this->biz['user']['id'],
            'reason' => empty($data['reason']) ? '' : $data['reason'],
            'amount' => $order['amount']
        ));
        $this->dispatch('order_refund.created', $orderRefund);
        return $orderRefund;
    }

    public function applyRefund($orderId, $data)
    {
        $orderRefund = $this->createOrderRefund($orderId, $data);

        $this->dispatch('order_refund.created', $orderRefund);

        return $orderRefund;
    }

    public function applyOrderItemsRefund($orderId, $orderItemIds, $data)
    {
        $orderRefund = $this->createOrderRefund($orderId, $data);
        $totalAmount = 0;
        $orderItemRefunds = array();
        foreach ($orderItemIds as $orderItemId) {
            $orderItem = $this->getOrderItemDao()->get($orderItemId);
            $orderItemRefund = $this->getOrderItemRefundDao()->create(array(
                'order_refund_id' => $orderRefund['id'],
                'order_id' => $orderRefund['order_id'],
                'order_item_id' => $orderItemId,
                'user_id' => $orderRefund['user_id'],
                'created_user_id' => $this->biz['user']['id'],
                'amount' => $orderItem['pay_amount']
            ));

            $totalAmount = $totalAmount + $orderItem['pay_amount'];

            $orderItemRefunds[] = $orderItemRefund;
        }

        $orderRefund = $this->getOrderRefundDao()->update($orderRefund['id'], array('amount' => $totalAmount));
        $orderRefund['orderItemRefunds'] = $orderItemRefunds;
        $this->dispatch('order_refund.created', $orderRefund);

        return $orderRefund;
    }

    public function adoptRefund($id, $data)
    {
        $this->validateLogin();
        $orderRefund = $this->getOrderRefundDao()->get($id);
        if (empty($orderRefund)) {
            throw $this->createNotFoundException("order_refund #{$id} is not found");
        }

        if ($orderRefund['status'] != 'created') {
            throw $this->createAccessDeniedException("order_refund #{$id} status is not created");
        }

        $orderRefund = $this->getOrderRefundDao()->update($id, array(
            'deal_time' => time(),
            'deal_user_id' => $this->biz['user']['id'],
            'deal_reason' => empty($data['deal_reason']) ? '' : $data['deal_reason'],
            'status' => 'adopt'
        ));

        $orderItemRefunds = $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefund['id']);
        foreach ($orderItemRefunds as $orderItemRefund) {
            $this->getOrderItemRefundDao()->update($orderItemRefund['id'], array(
                'status' => 'adopt'
            ));
        }

        $this->dispatch('order_refund.adopted', $orderRefund);
        return $orderRefund;
    }

    public function refuseRefund($id, $data)
    {
        $this->validateLogin();
        $orderRefund = $this->getOrderRefundDao()->get($id);
        if (empty($orderRefund)) {
            throw $this->createNotFoundException("order_refund #{$id} is not found");
        }

        if ($orderRefund['status'] != 'created') {
            throw $this->createAccessDeniedException("order_refund #{$id} status is not created");
        }

        $orderRefund = $this->getOrderRefundDao()->update($id, array(
            'deal_time' => time(),
            'deal_user_id' => $this->biz['user']['id'],
            'deal_reason' => empty($data['deal_reason']) ? '' : $data['deal_reason'],
            'status' => 'refused'
        ));

        $this->dispatch('order_refund.refused', $orderRefund);
        return $orderRefund;
    }

    public function finishRefund($id)
    {
        $this->validateLogin();
        $orderRefund = $this->getOrderRefundDao()->get($id);
        if (empty($orderRefund)) {
            throw $this->createNotFoundException("order_refund #{$id} is not found");
        }

        if ($orderRefund['status'] == 'finish') {
            return $orderRefund;
        }

        if ($orderRefund['status'] != 'adopt') {
            throw $this->createAccessDeniedException("order_refund #{$id} status is not adopt");
        }

        $orderRefund = $this->getOrderRefundDao()->update($id, array(
            'status' => 'finish'
        ));

        $orderItemRefunds = $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefund['id']);
        foreach ($orderItemRefunds as $orderItemRefund) {
            $this->getOrderItemRefundDao()->update($orderItemRefund['id'], array(
                'status' => 'finish'
            ));
        }

        $this->dispatch('order_refund.finished', $orderRefund);
        return $orderRefund;
    }

    protected function createOrderRefund($orderId, $data)
    {
        $this->validateLogin();
        $order = $this->getOrderDao()->get($orderId);
        if (empty($order)) {
            throw $this->createNotFoundException("order #{$orderId} is not found.");
        }

        if ($order['status'] != 'signed') {
            throw $this->createAccessDeniedException("order #${$order['id']} status is not signed.");
        }

        if ($this->biz['user']['id'] != $order['user_id']) {
            throw $this->createAccessDeniedException("order #{$orderId} can not refund.");
        }

        $orderRefund = $this->getOrderRefundDao()->create(array(
            'order_id' => $order['id'],
            'order_item_id' => 0,
            'sn' => $this->generateSn(),
            'user_id' => $order['user_id'],
            'created_user_id' => $this->biz['user']['id'],
            'reason' => empty($data['reason']) ? '' : $data['reason'],
            'amount' => $order['pay_amount'],
        ));

        return $orderRefund;
    }

    protected function validateLogin()
    {
        if (empty($this->biz['user']['id'])) {
            throw new AccessDeniedException('user is not login.');
        }
    }

    protected function generateSn()
    {
        return date('YmdHis', time()).mt_rand(10000, 99999);
    }

    protected function getOrderItemDao()
    {
        return $this->biz->dao('Order:OrderItemDao');
    }

    protected function getOrderDao()
    {
        return $this->biz->dao('Order:OrderDao');
    }

    protected function getOrderRefundDao()
    {
        return $this->biz->dao('Order:OrderRefundDao');
    }

    protected function getOrderItemRefundDao()
    {
        return $this->biz->dao('Order:OrderItemRefundDao');
    }
}