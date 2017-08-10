<?php

namespace Codeages\Biz\Framework\Order\Status;

class ConsignStatus extends AbstractStatus
{
    protected $status = 'consign';
    public function getPriorStatus()
    {
        return array('wait_consign');
    }

    public function process($orderId, $data)
    {
        return $this->getOrderDao()->update($orderId, array(
            'status' => 'consign',
        ));
    }
}