<?php


namespace Codeages\Biz\Framework\Event;


use Codeages\Biz\Framework\Context\Kernel;

class Event
{
    private $subject;
    private $argument;
    private $kernel;

    public function __construct(Kernel $kernel, $subject, $argument = array())
    {
        $this->subject = $subject;
        $this->argument = $argument;
        $this->kernel = $kernel;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getArgument()
    {
        return $this->argument;
    }

    public function getKernel()
    {
        return $this->kernel;
    }
}