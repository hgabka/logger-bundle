<?php

namespace Hgabka\LoggerBundle\EventListener;

use Hgabka\LoggerBundle\Helper\ExceptionNotifier;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    /** @var  ExceptionNotifier */
    protected $notifier;

    /**
     * ExceptionListener constructor.
     * @param ExceptionNotifier $notifier
     */
    public function __construct(ExceptionNotifier $notifier)
    {
        $this->notifier = $notifier;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();

        $this->notifier->trigger($exception);
    }
}