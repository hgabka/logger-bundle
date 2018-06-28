<?php

namespace Hgabka\LoggerBundle\Traits;

use Hgabka\LoggerBundle\Event\LogActionEvent;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;

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

    protected function logStart($type, $params = [], $object = null, $priority = null, $extraParameters = null)
    {
        $priority = $priority ?? Logger::getLevelName(Logger::INFO);

        $event = new LogActionEvent();
        $event
            ->setType($type)
            ->setParameters($params)
            ->setPriority($priority)
            ->setObject($object)
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

    protected function logUpdate($params = null, $object = null, $priority = null, $extraParameters = null)
    {
        $event = new LogActionEvent();
        $event->setParameters($params);
        if ($priority) {
            $event->setPriority($priority);
        }
        if ($extraParameters) {
            $event->setExtraParameters($extraParameters);
        }
        if ($object) {
            $event->setObject($object);
        }

        $this->dispatcher->dispatch(LogActionEvent::EVENT_UPDATE, $event);
    }

    protected function logFormErrors($type, $params, FormInterface $form, $object = null)
    {
        $this->logStart($type, $params, $object, Logger::ERROR);
        $this->logError($object, $form);
    }

    protected function logError($object = null, $extraParameters = null)
    {
        $event = new LogActionEvent();
        $event
            ->setParameters(null)
            ->setPriority(Logger::getLevelName(Logger::ERROR))
        ;
        if ($extraParameters) {
            if ($extraParameters instanceof FormInterface) {
                $extraParameters = ['errors' => (string) $extraParameters->getErrors(true, false)];
            } elseif (is_string($extraParameters)) {
                $extraParameters = ['errors' => $extraParameters];
            }

            if (!is_string($extraParameters)) {
                $extraParameters = json_encode($extraParameters);
            }
            $event->setExtraParameters($extraParameters);
        }

        if ($object) {
            $event->setObject($object);
        }

        $this->dispatcher->dispatch(LogActionEvent::EVENT_UPDATE, $event);
        $this->dispatcher->dispatch(LogActionEvent::EVENT_DONE, $event);
    }

    protected function actionLog($type, $params = [], $object = null, $priority = null, $extraParameters = null)
    {
        $this->logStart($type, $params, $object, $priority, $extraParameters);
        $this->logDone();
    }
}
