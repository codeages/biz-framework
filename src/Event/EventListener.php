<?php


namespace Codeages\Biz\Framework\Event;


class EventListener
{
    private $options;
    private $callable;
    private $eventName;

    /**
     * EventListener constructor.
     *
     * @param string   $eventName 监听的事件名
     * @param \Closure $callable  回调参数列表是Event类
     * @param array    $options   优先级的配置
     */
    public function __construct($eventName, $callable, array $options = array())
    {
        $this->eventName = $eventName;
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('$callable definition is not a Closure or invokable object.');
        }

        $this->callable = $callable;
        $this->options  = $options;
    }

    public function __invoke(Event $event)
    {
        call_user_func($this->callable, $event);
    }

    public function getEventName()
    {
        return $this->eventName;
    }

    public function getOptions()
    {
        return $this->options;
    }
}