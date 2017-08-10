<?php

namespace Codeages\Biz\Framework\Order\Subscriber;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'pay.success' => 'onPaid',
            'order.paid' => 'onOrderPaid',
            'trade.refunded' => 'onTradeRefunded'
        );
    }

    public function onTradeRefunded(Event $event)
    {
        $trade = $event->getSubject();
        $orderSn = $trade['order_sn'];
        $order = $this->getOrderService()->getOrderBySn($orderSn);
        $this->getOrderService()->finishRefund($order['id']);
    }

    public function onOrderPaid(Event $event)
    {
        $order = $event->getSubject();
        $this->getOrderProcess()->process($order);
    }

    public function getOrderProcess()
    {
        return $this->getBiz()->service('OrderProcess:CutOrderProcess');
    }

    public function onPaid(Event $event)
    {
        $trade = $event->getSubject();
        $args = $event->getArguments();
        $data = array(
            'trade_sn' => $trade['trade_sn'],
            'pay_time' => $args['paid_time'],
            'order_sn' => $trade['order_sn']
        );
        $this->getOrderService()->setOrderPaid($data);
    }

    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }
}