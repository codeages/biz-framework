<?php

namespace Codeages\Biz\Framework\Order\Status;

class PaidStatus extends AbstractStatus
{
    public $status = 'paid';

    public function getPriorStatus()
    {
        return array('created');
    }

    public function waitConsign()
    {
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => 'wait_consign'
        ));
        return $order;
    }
}