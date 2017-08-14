<?php

namespace Codeages\Biz\Framework\Order\Status;

class WaitConsignStatus extends AbstractStatus
{
    protected $status = 'wait_consign';

    public function getPriorStatus()
    {
        return array('paid');
    }

    public function consigned()
    {
        return $this->getOrderDao()->update($this->order['id'], array(
            'status' => 'consigned',
        ));
    }
}