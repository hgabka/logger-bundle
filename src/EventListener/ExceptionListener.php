<?php

namespace Hgabka\LoggerBundle\EventListener;

use Hgabka\LoggerBundle\Helper\ExceptionNotifier;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    /** @var ExceptionNotifier */
    protected $notifier;

    /**
     * ExceptionListener constructor.
     *
     * @param ExceptionNotifier $notifier
     */
    public function __construct(ExceptionNotifier $notifier)
    {
        $this->notifier = $notifier;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();

        $this->notifier->trigger($exception);
    }
}
