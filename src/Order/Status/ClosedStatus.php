<?php

namespace Codeages\Biz\Framework\Order\Status;

class ClosedStatus extends AbstractStatus
{
    const NAME = 'closed';

    public function getPriorStatus()
    {
        return array(CreatedStatus::NAME);
    }
}