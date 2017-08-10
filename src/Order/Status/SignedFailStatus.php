<?php

namespace Codeages\Biz\Framework\Order\Status;

class SignedFailStatus extends AbstractStatus
{
    protected $status = 'signed_fail';

    public function getPriorStatus()
    {
        return array('consign');
    }

    public function process($orderId, $data)
    {
        $this->getOrderDao()->get($orderId, array('lock'=>true));

        $signedTime = time();
        $order = $this->getOrderDao()->update($orderId, array(
            'status' => 'signed_fail',
            'signed_time' => $signedTime,
            'signed_data' => $data
        ));
        $items = $this->getOrderItemDao()->findByOrderId($orderId);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => 'signed_fail',
                'signed_time' => $signedTime,
                'signed_data' => $data
            ));
        }
        return $order;
    }

    protected function getOrderItemDao()
    {
        return $this->biz->dao('Order:OrderItemDao');
    }
}