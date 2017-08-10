<?php

namespace Codeages\Biz\Framework\Order\Status;

class WaitConsignStatus extends AbstractStatus
{
    protected $status = 'wait_consign';

    public function getPriorStatus()
    {
        return array('paid');
    }

    public function process($orderId, $data = array())
    {
        $order = $this->getOrderDao()->update($orderId, array(
            'status' => 'wait_consign'
        ));
        return $order;
    }
}