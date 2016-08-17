<?php


namespace Codeages\Biz\Framework\Tests\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventDispatcher;
use Codeages\Biz\Framework\Event\EventListener;
use Codeages\Biz\Framework\Tests\Example\ExampleKernel;

class EventTest extends \PHPUnit_Framework_TestCase
{
    private $kernel;

    public function __construct()
    {
        $this->kernel = new ExampleKernel();
        $this->kernel->boot();
    }

    public function testGetEventDispatcher()
    {
        $dispatcher = $this->kernel['EventDispatcher'];
        $this->assertNotNull($dispatcher);
        $this->assertTrue($dispatcher instanceof EventDispatcher);
    }

    public function testAddEventListener()
    {
        $testListener  = new EventListener('testAddEventListener', function (Event $event) {
        });
        $testListener2 = new EventListener('testAddEventListener', function (Event $event) {
        });
        $dispatcher    = $this->kernel['EventDispatcher'];

        $this->assertTrue($dispatcher
                ->addEventListener($testListener) instanceof EventDispatcher);
        $this->assertTrue($dispatcher
                ->addEventListener($testListener2) instanceof EventDispatcher);
    }

    public function testEventDispatch()
    {
        $dispatcher = $this->kernel['EventDispatcher'];
        $self       = $this;

        $value = 0;

        $testListener = new EventListener('test1', function (Event $event) use ($self, &$value) {
            $self->assertEquals($event->getSubject(), 1);
            $argument = $event->getArgument();
            $self->assertEquals($argument['testKey'], 10);
            $self->assertEquals($value, 1);
        }, array(
            'level' => 1
        ));

        $testListener2 = new EventListener('test1', function (Event $event) use (&$value, $self) {
            $value += 1;
        }, array(
            'level' => 0
        ));

        $dispatcher
            ->addEventListener($testListener)
            ->addEventListener($testListener2);

        $event = $dispatcher->createEvent(1, array('testKey' => 10));

        $dispatcher->dispatch('test1', $event);
    }
}