<?php

namespace Hgabka\LoggerBundle\Controller;

use Hgabka\LoggerBundle\Event\LogActionEvent;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LoggableController extends Controller
{
    protected function logStart($type, $params = [], $priority = null)
    {
        $priority = $priority ?? Logger::getLevelName(Logger::INFO);

        $event = new LogActionEvent();
        $event
            ->setType($type)
            ->setParameters($params)
            ->setPriority($priority)
        ;

        $this->get('event_dispatcher')->dispatch(LogActionEvent::EVENT_START, $event);
    }

    protected function logDone()
    {
        $event = new LogActionEvent();

        $this->get('event_dispatcher')->dispatch(LogActionEvent::EVENT_DONE, $event);
    }

    protected function logUpdate()
    {
        $event = new LogActionEvent();

        $this->get('event_dispatcher')->dispatch(LogActionEvent::EVENT_UPDATE, $event);
    }

    protected function logError()
    {
        $event = new LogActionEvent();
        $event
            ->setParameters(null)
            ->setPriority(Logger::getLevelName(Logger::ERROR))
        ;

        $this->get('event_dispatcher')->dispatch(LogActionEvent::EVENT_UPDATE, $event);
        $this->get('event_dispatcher')->dispatch(LogActionEvent::EVENT_DONE, $event);
    }

    protected function actionLog($type, $params = [], $priority = null)
    {
        $this->logStart($type, $params, $priority);
        $this->logDone();
    }
}
