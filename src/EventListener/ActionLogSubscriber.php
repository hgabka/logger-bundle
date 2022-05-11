<?php

namespace Hgabka\LoggerBundle\EventListener;

use Hgabka\LoggerBundle\Event\LogActionEvent;
use Hgabka\LoggerBundle\Logger\ActionLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionLogSubscriber implements EventSubscriberInterface
{
    protected ActionLogger $logger;

    /**
     * ActionLogSubscriber constructor.
     *
     * @param ActionLogger $logger
     */
    public function __construct(ActionLogger $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogActionEvent::EVENT_START => 'onStart',
            LogActionEvent::EVENT_DONE => 'onDone',
            LogActionEvent::EVENT_UPDATE => 'onUpdate',
            LogActionEvent::EVENT_LOG => 'onLog',
        ];
    }

    public function onStart(LogActionEvent $event): void
    {
        $this->logger->start($event->getType(), $event->getParameters(), $event->getObject(), $event->getExtraParameters());
    }

    public function onUpdate(LogActionEvent $event): void
    {
        $this->logger->update($event->getParameters(), $event->getObject(), $event->getPriority(), $event->getExtraParameters());
    }

    public function onDone(LogActionEvent $event): void
    {
        $this->logger->done();
    }

    public function onLog(LogActionEvent $event): void
    {
        $this->logger->log($event->getType(), $event->getParameters(), $event->getObject(), $event->getPriority(), $event->getExtraParameters());
    }
}
