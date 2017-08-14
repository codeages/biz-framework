<?php

namespace Codeages\Biz\Framework\Order\Status;

class ConsignedStatus extends AbstractStatus
{
    protected $status = 'consigned';

    public function getPriorStatus()
    {
        return array('wait_consign');
    }

    public function signed($data = array())
    {
        $signedTime = time();
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => 'signed',
            'signed_time' => $signedTime,
            'signed_data' => $data
        ));
        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => 'signed',
                'signed_time' => $signedTime,
                'signed_data' => $data
            ));
        }
        return $order;
    }

    public function signedFail($data = array())
    {
        $this->getOrderDao()->get($this->order['id'], array('lock'=>true));

        $signedTime = time();
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => 'signed_fail',
            'signed_time' => $signedTime,
            'signed_data' => $data
        ));
        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => 'signed_fail',
                'signed_time' => $signedTime,
                'signed_data' => $data
            ));
        }
        return $order;
    }
}