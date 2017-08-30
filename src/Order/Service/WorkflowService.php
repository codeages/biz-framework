<?php

namespace Codeages\Biz\Framework\Order\Service;

interface WorkflowService
{
    public function start($order, $orderItems);

    public function close($id, $data = array());

    public function paying($id, $data = array());

    public function paid($data);

    public function finish($id, $data = array());

    public function fail($id, $data = array());

    public function refunding($id, $data = array());

    public function refunded($id, $data = array());
}