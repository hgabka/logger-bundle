<?php

namespace Hgabka\LoggerBundle\Tests\Event;

use Hgabka\LoggerBundle\Event\LogActionEvent;
use PHPUnit\Framework\TestCase;

class LogActionEventTest extends TestCase
{
    public function testSettersAreFluentAndValuesAreStored(): void
    {
        $object = new \stdClass();
        $event = new LogActionEvent();

        $result = $event
            ->setType('order.save')
            ->setParameters(['%id%' => 5])
            ->setPriority('ERROR')
            ->setExtraParameters('extra')
            ->setObject($object)
        ;

        $this->assertSame($event, $result);
        $this->assertSame('order.save', $event->getType());
        $this->assertSame(['%id%' => 5], $event->getParameters());
        $this->assertSame('ERROR', $event->getPriority());
        $this->assertSame('extra', $event->getExtraParameters());
        $this->assertSame($object, $event->getObject());
    }

    public function testEventNamesAreDistinct(): void
    {
        $names = [
            LogActionEvent::EVENT_START,
            LogActionEvent::EVENT_DONE,
            LogActionEvent::EVENT_UPDATE,
            LogActionEvent::EVENT_LOG,
            LogActionEvent::EVENT_FORM,
        ];

        $this->assertSame($names, array_unique($names));
        $this->assertSame('hgabka_logger.log_start', LogActionEvent::EVENT_START);
    }
}
