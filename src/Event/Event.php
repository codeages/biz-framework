<?php


namespace Codeages\Biz\Framework\Event;


use Codeages\Biz\Framework\Context\Kernel;
use Symfony\Component\EventDispatcher\GenericEvent;

class Event extends GenericEvent
{
    protected $kernel;

    public function __construct(Kernel $kernel, $subject, array $arguments)
    {
        $this->kernel = $kernel;
        parent::__construct($subject, $arguments);
    }

    public function getKernel()
    {
        return $this->kernel;
    }
}