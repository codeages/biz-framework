<?php

namespace Codeages\Biz\Framework\Order\Status;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

abstract class AbstractStatus
{
    protected $biz;
    protected $order;

    function __construct($biz)
    {
        $this->biz = $biz;
    }

    abstract public function getPriorStatus();

    public function setOrder($order)
    {
        $this->order = $order;
    }

    protected function getOrderDao()
    {
        return $this->biz->dao('Order:OrderDao');
    }

    protected function getOrderItemDao()
    {
        return $this->biz->dao('Order:OrderItemDao');
    }
}
