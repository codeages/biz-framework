<?php

namespace Codeages\Biz\Framework\Order\Status;

class FinishStatus extends AbstractStatus
{
    protected $status = 'finish';

    public function getPriorStatus()
    {
        return array('signed');
    }
}