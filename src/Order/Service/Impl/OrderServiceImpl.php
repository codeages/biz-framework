<?php

namespace Codeages\Biz\Framework\Order\Service\Impl;

use Codeages\Biz\Framework\Order\Service\OrderService;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\Biz\Framework\Targetlog\Service\TargetlogService;

class OrderServiceImpl extends BaseService implements OrderService
{
    public function createOrder($order, $orderItems)
    {
        $this->validateLogin();
        $orderItems = $this->validateFields($order, $orderItems);
        $order = ArrayToolkit::parts($order, array(
            'title',
            'callback',
            'source',
            'user_id',
            'created_reason',
            'seller_id',
            'price_type'
        ));

        try {
            $this->beginTransaction();
            $order = $this->saveOrder($order, $orderItems);
            $order = $this->createOrderItems($order, $orderItems);
            $this->commit();
        } catch (AccessDeniedException $e) {
            $this->rollback();
            throw $e;
        } catch (InvalidArgumentException $e) {
            $this->rollback();
            throw $e;
        } catch (NotFoundException $e) {
            $this->rollback();
            throw $e;
        } catch (\Exception $e) {
            $this->rollback();
            throw new ServiceException($e);
        }

        $this->dispatch('order.created', $order);
        $this->getTargetlogService()->log(TargetlogService::INFO, 'order.created', $order['sn'], "创建订单{$order['sn']}", $order);
        return $order;
    }

    protected function saveOrder($order, $items)
    {
        $user = $this->biz['user'];
        $order['sn'] = $this->generateSn();
        $order['price_amount'] = $this->countOrderPriceAmount($items);
        $order['pay_amount'] = $this->countOrderPayAmount($order['price_amount'], $items);
        $order['created_user_id'] = $user['id'];
        $order = $this->getOrderDao()->create($order);
        return $order;
    }

    protected function countOrderPriceAmount($items)
    {
        $priceAmount = 0;
        foreach ($items as $item) {
            $priceAmount = $priceAmount + $item['price_amount'];
        }
        return $priceAmount;
    }

    protected function countOrderPayAmount($payAmount, $items)
    {
        foreach ($items as $item) {
            if (empty($item['deducts'])) {
                continue;
            }

            foreach ($item['deducts'] as $deduct) {
                $payAmount = $payAmount - $deduct['deduct_amount'];
            }
        }

        if ($payAmount<0) {
            $payAmount = 0;
        }

        return $payAmount;
    }

    protected function generateSn()
    {
        return date('YmdHis', time()).mt_rand(10000, 99999);
    }

    protected function createOrderItems($order, $items)
    {
        $savedItems = array();
        $savedDeducts = array();
        foreach ($items as $item) {
            $deducts = array();
            if (!empty($item['deducts'])) {
                $deducts = $item['deducts'];
                unset($item['deducts']);
            }
            $item['order_id'] = $order['id'];
            $item['seller_id'] = $order['seller_id'];
            $item['user_id'] = $order['user_id'];
            $item['sn'] = $this->generateSn();
            $item['pay_amount'] = $this->countOrderItemPayAmount($item, $deducts);
            $item = $this->getOrderItemDao()->create($item);
            array_merge($savedDeducts, $this->createOrderItemDeducts($item, $deducts));
            $savedItems[] = $item;
        }

        $order['items'] = $savedItems;
        $order['deducts'] = $savedDeducts;
        return $order;
    }

    protected function countOrderItemPayAmount($item, $deducts)
    {
        $priceAmount = $item['price_amount'];

        foreach ($deducts as $deduct) {
            $priceAmount = $priceAmount - $deduct['deduct_amount'];
        }

        return $priceAmount;
    }

    protected function createOrderItemDeducts($item, $deducts)
    {
        $savedDeducts = array();
        foreach ($deducts as $deduct) {
            $deduct['item_id'] = $item['id'];
            $deduct['order_id'] = $item['order_id'];
            $deduct['seller_id'] = $item['seller_id'];
            $deduct['user_id'] = $item['user_id'];
            $savedDeducts[] = $this->getOrderItemDeductDao()->create($deduct);
        }
        return $savedDeducts;
    }

    protected function validateLogin()
    {
        if (empty($this->biz['user']['id'])) {
            throw new AccessDeniedException('user is not login.');
        }
    }

    protected function validateFields($order, $orderItems)
    {
        if (!ArrayToolkit::requireds($order, array('user_id'))) {
            throw new InvalidArgumentException('user_id is required in order.');
        }

        foreach ($orderItems as $item) {
            if (!ArrayToolkit::requireds($item, array(
                'title',
                'price_amount',
                'target_id',
                'target_type'))) {
                throw new InvalidArgumentException('args is invalid.');
            }
        }

        return $orderItems;
    }

    public function setOrderPaid($data)
    {
        $data = ArrayToolkit::parts($data, array(
            'order_sn',
            'trade_sn',
            'pay_time'
        ));

        try {
            $this->beginTransaction();
            $order = $this->payOrder($data);
            $this->payOrderItems($order);
            $this->commit();
            $this->getTargetlogService()->log(TargetlogService::INFO, 'order.paid', $order['sn'], "订单支付成功，订单号：{$order['sn']}，订单状态为{$order['status']}", $order);
        } catch (AccessDeniedException $e) {
            $this->rollback();
            throw $e;
        } catch (InvalidArgumentException $e) {
            $this->rollback();
            throw $e;
        } catch (NotFoundException $e) {
            $this->rollback();
            throw $e;
        } catch (\Exception $e) {
            $this->rollback();
            throw new ServiceException($e);
        }
        $this->dispatch('order.paid', $order);
    }

    protected function payOrder($data)
    {
        $order = $this->getOrderBySn($data['order_sn'], true);
        $data = ArrayToolkit::parts($data, array(
            'trade_sn',
            'pay_time'
        ));
        $data['status'] = 'paid';
        return $this->getOrderDao()->update($order['id'], $data);
    }

    protected function payOrderItems($order)
    {
        $items = $this->getOrderItemDao()->findByOrderId($order['id']);
        $fields = ArrayToolkit::parts($order, array('status'));
        $fields['pay_time'] = $order['pay_time'];
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], $fields);
        }
    }

    public function findOrderItemsByOrderId($orderId)
    {
        return $this->getOrderItemDao()->findByOrderId($orderId);
    }

    public function findOrderItemDeductsByItemId($itemId)
    {
        return $this->getOrderItemDeductDao()->findByItemId($itemId);
    }

    public function closeOrder($id)
    {
        try {
            $this->beginTransaction();
            $order = $this->getOrderDao()->get($id, array('lock' => true));
            if ('created' != $order['status']) {
                throw $this->createAccessDeniedException('status is not created.');
            }

            $closeTime = time();
            $order = $this->getOrderDao()->update($id, array(
                'status' => 'close',
                'close_time' => $closeTime
            ));

            $items = $this->findOrderItemsByOrderId($id);
            foreach ($items as $item) {
                $this->getOrderItemDao()->update($item['id'], array(
                    'status' => 'close',
                    'close_time' => $closeTime
                ));
            }
            $this->commit();
        } catch (AccessDeniedException $e) {
            $this->rollback();
            throw $e;
        } catch (InvalidArgumentException $e) {
            $this->rollback();
            throw $e;
        } catch (NotFoundException $e) {
            $this->rollback();
            throw $e;
        } catch (\Exception $e) {
            $this->rollback();
            throw new ServiceException($e->getMessage());
        }
        $this->dispatch('order.closed', $order);

        $this->getTargetlogService()->log(TargetlogService::INFO, 'order.close', $order['sn'], "关闭订单，订单号：{$order['sn']}", $order);
        return $order;
    }

    public function closeOrders()
    {
        $orders = $this->getOrderDao()->search(array(
            'created_time_LT' => time()-2*60*60
        ), array('id'=>'DESC'), 0, 1000);

        foreach ($orders as $order) {
            $this->closeOrder($order['id']);
        }
    }

    public function finishOrder($id)
    {
        try {
            $this->beginTransaction();
            $order = $this->getOrderDao()->get($id, array('lock'=>true));
            if ('signed' != $order['status']) {
                throw $this->createAccessDeniedException('status is not paid.');
            }

            $finishTime = time();
            $order = $this->getOrderDao()->update($id, array(
                'status' => 'finish',
                'finish_time' => $finishTime
            ));

            $items = $this->findOrderItemsByOrderId($id);
            foreach ($items as $item) {
                $this->getOrderItemDao()->update($item['id'], array(
                    'status' => 'finish',
                    'finish_time' => $finishTime
                ));
            }
            $this->commit();
        } catch (AccessDeniedException $e) {
            $this->rollback();
            throw $e;
        } catch (InvalidArgumentException $e) {
            $this->rollback();
            throw $e;
        } catch (NotFoundException $e) {
            $this->rollback();
            throw $e;
        } catch (\Exception $e) {
            $this->rollback();
            throw new ServiceException($e->getMessage());
        }

        $this->dispatch('order.finished', $order);

        $this->getTargetlogService()->log(TargetlogService::INFO, 'order.finish', $order['sn'], "完成订单，订单号：{$order['sn']}", $order);
        return $order;
    }

    public function finishOrders()
    {
        $orders = $this->getOrderDao()->search(array(
            'pay_time_LT' => time()-2*60*60,
            'status' => 'paid'
        ), array('id'=>'DESC'), 0, 1000);

        foreach ($orders as $order) {
            $this->finishOrder($order['id']);
        }
    }

    public function signSuccessOrder($id, $data)
    {
        return $this->signOrder($id, 'signed', $data);
    }

    public function signFailOrder($id, $data)
    {
        return $this->signOrder($id, 'signed_fail', $data);
    }

    protected function signOrder($id, $status, $data)
    {
        try {
            $this->beginTransaction();
            $order = $this->getOrderDao()->get($id, array('lock'=>true));
            if ('paid' != $order['status']) {
                throw $this->createAccessDeniedException('status is not paid.');
            }

            $signedTime = time();
            $order = $this->getOrderDao()->update($id, array(
                'status' => $status,
                'signed_time' => $signedTime,
                'signed_data' => $data
            ));

            $items = $this->findOrderItemsByOrderId($id);
            foreach ($items as $item) {
                $this->getOrderItemDao()->update($item['id'], array(
                    'status' => $status,
                    'signed_time' => $signedTime,
                    'signed_data' => $data
                ));
            }
            $this->commit();
        } catch (AccessDeniedException $e) {
            $this->rollback();
            throw $e;
        } catch (InvalidArgumentException $e) {
            $this->rollback();
            throw $e;
        } catch (NotFoundException $e) {
            $this->rollback();
            throw $e;
        } catch (\Exception $e) {
            $this->rollback();
            throw new ServiceException($e->getMessage());
        }

        $this->dispatch("order.{$status}", $order);

        $this->getTargetlogService()->log(TargetlogService::INFO, "order.{$status}", $order['sn'], "签收订单{$status}，订单号：{$order['sn']}", $order);
        return $order;
    }

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
        $this->validateLogin();
        $order = $this->getOrderDao()->get($orderId);
        if (empty($order)) {
            throw $this->createNotFoundException("order #{$orderId} is not found.");
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
            'amount' => $order['pay_amount']
        ));

        $this->dispatch('order_refund.created', $orderRefund);

        return $orderRefund;
    }

    public function finishRefund($id, $data)
    {
        $this->validateLogin();
        $orderRefund = $this->getOrderRefundDao()->get($id);
        if (empty($orderRefund)) {
            throw $this->createNotFoundException("order_refund #{$id} is not found");
        }

        $orderRefund = $this->getOrderRefundDao()->update($id, array(
            'deal_time' => time(),
            'deal_user_id' => $this->biz['user']['id'],
            'deal_reason' => empty($data['deal_reason']) ? '' : $data['deal_reason'],
            'status' => 'finish'
        ));

        $this->dispatch('order_refund.finished', $orderRefund);
        return $orderRefund;
    }

    public function refuseRefund($id, $data)
    {
        $this->validateLogin();
        $orderRefund = $this->getOrderRefundDao()->get($id);
        if (empty($orderRefund)) {
            throw $this->createNotFoundException("order_refund #{$id} is not found");
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

    public function getOrder($id)
    {
        return $this->getOrderDao()->get($id);
    }

    public function getOrderBySn($sn, $lock = false)
    {
        return $this->getOrderDao()->getBySn($sn, array('lock' => $lock));
    }

    public function searchOrders($conditions, $orderBy, $start, $limit)
    {
        return $this->getOrderDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countOrders($conditions)
    {
        return $this->getOrderDao()->count($conditions);
    }

    public function searchOrderItems($conditions, $orderBy, $start, $limit)
    {
        return $this->getOrderItemDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countOrderItems($conditions)
    {
        return $this->getOrderItemDao()->count($conditions);
    }

    public function findOrdersByIds(array $ids)
    {
        return $this->getOrderDao()->findByIds($ids);
    }

    protected function getOrderDao()
    {
        return $this->biz->dao('Order:OrderDao');
    }

    protected function getOrderRefundDao()
    {
        return $this->biz->dao('Order:OrderRefundDao');
    }

    protected function getOrderItemDao()
    {
        return $this->biz->dao('Order:OrderItemDao');
    }

    protected function getOrderItemDeductDao()
    {
        return $this->biz->dao('Order:OrderItemDeductDao');
    }

    protected function getTargetlogService()
    {
        return $this->biz->service('Targetlog:TargetlogService');
    }
}