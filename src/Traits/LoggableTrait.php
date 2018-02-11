<?php

namespace Hgabka\LoggerBundle\Traits;

use Hgabka\LoggerBundle\Event\LogActionEvent;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

trait LoggableTrait
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @required
     *
     * @param BreadcrumbManager $dispatcher
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    protected function logStart($type, $params = [], $priority = null, $extraParameters = null)
    {
        $priority = $priority ?? Logger::getLevelName(Logger::INFO);

        $event = new LogActionEvent();
        $event
            ->setType($type)
            ->setParameters($params)
            ->setPriority($priority)
        ;
        if ($extraParameters) {
            $event->setExtraParameters($extraParameters);
        }

        $this->dispatcher->dispatch(LogActionEvent::EVENT_START, $event);
    }

    protected function logDone()
    {
        $event = new LogActionEvent();

        $this->dispatcher->dispatch(LogActionEvent::EVENT_DONE, $event);
    }

    protected function logUpdate($priority = null, $extraParameters = null)
    {
        $event = new LogActionEvent();
        if ($priority) {
            $event->setPriority($priority);
        }
        if ($extraParameters) {
            $event->setExtraParameters($extraParameters);
        }

        $this->dispatcher->dispatch(LogActionEvent::EVENT_UPDATE, $event);
    }

    protected function logError($extraParameters = null)
    {
        $event = new LogActionEvent();
        $event
            ->setParameters(null)
            ->setPriority(Logger::getLevelName(Logger::ERROR))
        ;
        if ($extraParameters) {
            $event->setExtraParameters($extraParameters);
        }

        $this->dispatcher->dispatch(LogActionEvent::EVENT_UPDATE, $event);
        $this->dispatcher->dispatch(LogActionEvent::EVENT_DONE, $event);
    }

    protected function actionLog($type, $params = [], $priority = null, $extraParameters = null)
    {
        $this->logStart($type, $params, $priority, $extraParameters);
        $this->logDone();
    }
}
