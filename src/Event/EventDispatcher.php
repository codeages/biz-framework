<?php


namespace Codeages\Biz\Framework\Event;


use Codeages\Biz\Framework\Context\Kernel;

class EventDispatcher
{
    private        $listeners;
    private static $instance;
    private        $kernel;

    private function __construct()
    {
        $this->listeners = array();
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new EventDispatcher();
        }

        return self::$instance;
    }

    public function setKernel(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function dispatch($eventName, Event $event)
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $listeners){
            array_walk($listeners, function ($listener) use ($event){
                call_user_func($listener, $event);
            });
        }

    }

    public function addEventListener(EventListener $listener)
    {
        $options = $listener->getOptions();
        if(isset($options['level']) && is_int($options['level'])){
            $this->listeners[$listener->getEventName()][$options['level']][] = $listener;
            uksort($this->listeners[$listener->getEventName()], function ($a, $b){
                return $a > $b;
            });
        }else{
            $this->listeners[$listener->getEventName()][][] = $listener;
        }

        return $this;
    }

    public function createEvent($subject, $argument = array())
    {
        return new Event($this->kernel, $subject, $argument);
    }
}