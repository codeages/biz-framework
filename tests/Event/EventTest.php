<?php


namespace Codeages\Biz\Framework\Tests\Event;

use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Tests\Example\ExampleKernel;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventTest extends \PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        $this->kernel = new ExampleKernel();
        $this->kernel->boot();
    }

    public function testDispatch()
    {
        $this->kernel->addEventSubscriber(new TestEventSubscriber($this->kernel));
        $this->kernel->dispatch('foo', 1);
    }
}

class TestEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            'foo' => 'bar'
        );
    }

    public function bar(Event $event)
    {
        $subject = $event->getSubject();
        $kernel = $this->getKernel();
    }

}