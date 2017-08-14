<?php

namespace Codeages\Biz\Framework\Order\Status;

class SignedStatus extends AbstractStatus
{
    protected $status = 'signed';

    public function getPriorStatus()
    {
        return array('consigned');
    }

    public function finish()
    {
        $finishTime = time();
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => 'finish',
            'finish_time' => $finishTime
        ));

        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => 'finish',
                'finish_time' => $finishTime
            ));
        }
        return $order;
    }

    protected function getOrderItemDao()
    {
        return $this->biz->dao('Order:OrderItemDao');
    }
}