<?php

namespace Codeages\Biz\Framework\Order\Status;

class SignedFailStatus extends AbstractStatus
{
    const NAME = 'signed_fail';

    public function getPriorStatus()
    {
        return array(ConsignedStatus::NAME);
    }
}