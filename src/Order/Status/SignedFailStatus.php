<?php

namespace Codeages\Biz\Framework\Order\Status;

class SignedFailStatus extends AbstractStatus
{
    protected $status = 'signed_fail';

    public function getPriorStatus()
    {
        return array('consigned');
    }
}