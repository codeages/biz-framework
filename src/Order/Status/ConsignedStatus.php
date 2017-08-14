<?php

namespace Codeages\Biz\Framework\Order\Status;

class ConsignedStatus extends AbstractStatus
{
    const NAME = 'consigned';

    public function getPriorStatus()
    {
        return array(WaitConsignStatus::NAME);
    }

    public function signed($data = array())
    {
        return $this->processSign(SignedStatus::NAME, $data);
    }

    public function signedFail($data = array())
    {
        return $this->processSign(SignedFailStatus::NAME, $data);
    }

    protected function processSign($status, $data)
    {
        $this->getOrderDao()->get($this->order['id'], array('lock'=>true));

        $signedTime = time();
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => $status,
            'signed_time' => $signedTime,
            'signed_data' => $data
        ));
        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => $status,
                'signed_time' => $signedTime,
                'signed_data' => $data
            ));
        }
        return $order;
    }
}