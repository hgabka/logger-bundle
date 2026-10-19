<?php

namespace Hgabka\LoggerBundle\Tests\EventListener;

use Hgabka\LoggerBundle\Event\LogActionEvent;
use Hgabka\LoggerBundle\EventListener\ActionLogSubscriber;
use Hgabka\LoggerBundle\Logger\ActionLogger;
use PHPUnit\Framework\TestCase;

class ActionLogSubscriberTest extends TestCase
{
    private function event(): LogActionEvent
    {
        $event = new LogActionEvent();

        return $event->setType('t')->setParameters(['p'])->setObject($o = new \stdClass())->setPriority('INFO')->setExtraParameters('x');
    }

    public function testSubscribedEvents(): void
    {
        $this->assertSame(
            [
                LogActionEvent::EVENT_START => 'onStart',
                LogActionEvent::EVENT_DONE => 'onDone',
                LogActionEvent::EVENT_UPDATE => 'onUpdate',
                LogActionEvent::EVENT_LOG => 'onLog',
            ],
            ActionLogSubscriber::getSubscribedEvents()
        );
    }

    public function testEventsAreForwardedToTheLogger(): void
    {
        $event = $this->event();
        $logger = $this->createMock(ActionLogger::class);
        $logger->expects($this->once())->method('start')->with('t', ['p'], $event->getObject(), 'x');
        $logger->expects($this->once())->method('update')->with(['p'], $event->getObject(), 'INFO', 'x');
        $logger->expects($this->once())->method('log')->with('t', ['p'], $event->getObject(), 'INFO', 'x');
        $logger->expects($this->once())->method('done');

        $subscriber = new ActionLogSubscriber($logger);
        $subscriber->onStart($event);
        $subscriber->onUpdate($event);
        $subscriber->onLog($event);
        $subscriber->onDone($event);
    }
}
