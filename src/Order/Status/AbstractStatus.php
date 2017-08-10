<?php

namespace Codeages\Biz\Framework\Order\Status;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

abstract class AbstractStatus
{
    protected $status;
    protected $biz;

    function __construct($biz)
    {
        $this->biz = $biz;
    }

    abstract public function getPriorStatus();

    abstract public function process($orderId, $data = array());

    public function __call($method, $arguments)
    {
        $status = $this->getNextStatusName($method);
        $nextStatusProcessor = StatusFactory::instance($this->biz)->getStatusProcessor($status);

        if (!in_array($this->status, $nextStatusProcessor->getPriorStatus())) {
            throw new AccessDeniedException("can't change {$this->status} to {$status}.");
        }

        try {
//            $this->biz['db']->beginTransaction();
            $order = $nextStatusProcessor->process($arguments[0], $arguments[1]);
//            $this->biz['db']->commit();
        } catch (AccessDeniedException $e) {
//            $this->biz['db']->rollback();
            throw $e;
        } catch (InvalidArgumentException $e) {
//            $this->biz['db']->rollback();
            throw $e;
        } catch (NotFoundException $e) {
//            $this->biz['db']->rollback();
            throw $e;
        } catch (\Exception $e) {
//            $this->biz['db']->rollback();
            throw new ServiceException($e->getMessage());
        }

        $this->createOrderLog($order);
        $this->dispatch("order.{$status}", $order);
        return $order;
    }

    private function getNextStatusName($method)
    {
        $prefix = 'set';
        $suffix = 'Order';
        $status = substr($method, strlen($prefix),strlen($method) - strlen($prefix));
        $status = substr($status,0,strlen($status) - strlen($suffix));
        return $this->humpToLine($status);
    }

    private function humpToLine($str){
        $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
            return '_'.strtolower($matches[0]);
        },$str);

        if (strpos($str , '_') === 0) {
            return substr($str,1,strlen($str));
        }

        return $str;
    }

    protected function createOrderLog($order, $dealData = array())
    {
        $orderLog = array(
            'status' => $order['status'],
            'order_id' => $order['id'],
            'user_id' => $this->biz['user']['id'],
            'deal_data' => $dealData
        );
        return $this->getOrderLogDao()->create($orderLog);
    }

    private function getDispatcher()
    {
        return $this->biz['dispatcher'];
    }

    protected function dispatch($eventName, $subject, $arguments = array())
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        return $this->getDispatcher()->dispatch($eventName, $event);
    }

    protected function getOrderDao()
    {
        return $this->biz->dao('Order:OrderDao');
    }

    protected function getOrderLogDao()
    {
        return $this->biz->dao('Order:OrderLogDao');
    }
}
