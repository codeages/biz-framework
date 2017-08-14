<?php

namespace Codeages\Biz\Framework\Order\Status;

class FinishStatus extends AbstractStatus
{
    const NAME = 'finish';

    public function getPriorStatus()
    {
        return array(SignedStatus::NAME);
    }
}