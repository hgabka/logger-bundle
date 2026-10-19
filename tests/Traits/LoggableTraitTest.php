<?php

namespace Hgabka\LoggerBundle\Tests\Traits;

use Hgabka\LoggerBundle\Event\LogActionEvent;
use Hgabka\LoggerBundle\Traits\LoggableTrait;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class LoggableTraitTest extends TestCase
{
    /** @var list<array{string, LogActionEvent}> */
    private array $received = [];

    private object $subject;

    protected function setUp(): void
    {
        $dispatcher = new EventDispatcher();
        foreach ([LogActionEvent::EVENT_START, LogActionEvent::EVENT_DONE, LogActionEvent::EVENT_UPDATE] as $name) {
            $dispatcher->addListener($name, function (LogActionEvent $event) use ($name): void {
                $this->received[] = [$name, $event];
            });
        }

        $this->subject = new class() {
            use LoggableTrait {
                logStart as public;
                logDone as public;
                logUpdate as public;
                logError as public;
                logFormErrors as public;
                actionLog as public;
            }
        };
        $this->subject->setDispatcher($dispatcher);
    }

    public function testLogStartDispatchesStartEventWithDefaults(): void
    {
        $object = new \stdClass();
        $this->subject->logStart('order.save', ['%id%' => 1], $object);

        [$name, $event] = $this->received[0];
        $this->assertSame(LogActionEvent::EVENT_START, $name);
        $this->assertSame('order.save', $event->getType());
        $this->assertSame(['%id%' => 1], $event->getParameters());
        $this->assertSame($object, $event->getObject());
        $this->assertSame(Logger::getLevelName(Logger::INFO), $event->getPriority());
    }

    public function testLogUpdateOnlyCarriesGivenValues(): void
    {
        $this->subject->logUpdate(['step' => 2], null, 'WARNING');

        [$name, $event] = $this->received[0];
        $this->assertSame(LogActionEvent::EVENT_UPDATE, $name);
        $this->assertSame(['step' => 2], $event->getParameters());
        $this->assertSame('WARNING', $event->getPriority());
        $this->assertNull($event->getObject());
    }

    public function testActionLogDispatchesStartThenDone(): void
    {
        $this->subject->actionLog('ping');

        $this->assertSame(
            [LogActionEvent::EVENT_START, LogActionEvent::EVENT_DONE],
            array_column($this->received, 0)
        );
    }

    public function testLogErrorEncodesArraysAsJson(): void
    {
        $this->subject->logError(null, ['message' => 'Árvíztűrő']);

        [$name, $event] = $this->received[0];
        $this->assertSame(LogActionEvent::EVENT_UPDATE, $name);
        $this->assertSame(Logger::getLevelName(Logger::ERROR), $event->getPriority());
        $this->assertSame('{"message":"Árvíztűrő"}', $event->getExtraParameters());
    }

    public function testLogErrorWrapsPlainStrings(): void
    {
        $this->subject->logError(null, 'boom');

        $this->assertSame('{"errors":"boom"}', $this->received[0][1]->getExtraParameters());
    }

    public function testLogFormErrorsStartsAnErrorActionAndLogsFormErrors(): void
    {
        $form = (new FormFactoryBuilder())->getFormFactory()->createBuilder(FormType::class)->add('name', TextType::class)->getForm();

        $this->subject->logFormErrors('form.save', [], $form);

        $this->assertSame(
            [LogActionEvent::EVENT_START, LogActionEvent::EVENT_UPDATE],
            array_column($this->received, 0)
        );
        $this->assertSame(Logger::getLevelName(Logger::ERROR), $this->received[1][1]->getPriority());
    }
}
