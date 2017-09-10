<?php

namespace Hgabka\LoggerBundle\Helper;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Hgabka\LoggerBundle\Entity\Notify;
use Hgabka\LoggerBundle\Entity\NotifyCall;
use Hgabka\LoggerBundle\Logger\ExceptionLogger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionNotifier
{
    /** @var Registry */
    protected $doctrine;

    /** @var \Swift_Mailer */
    protected $mailer;

    /** @var ExceptionLogger */
    protected $logger;

    /** @var RequestStack */
    protected $requestStack;

    /** @var  array */
    protected $config;

    /** @var  bool */
    protected $isDebug;

    /**
     * ExceptionNotifier constructor.
     * @param Registry $doctrine
     * @param \Swift_Mailer $mailer
     * @param RequestStack $requestStack
     * @param ExceptionLogger $logger
     * @param bool $isDebug
     */
    public function __construct(Registry $doctrine, \Swift_Mailer $mailer, RequestStack $requestStack, ExceptionLogger $logger, bool $isDebug)
    {
        $this->doctrine = $doctrine;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->isDebug = $isDebug;
        $this->requestStack = $requestStack;
    }

    /**
     * @param array $config
     * @return ExceptionNotifier
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function isFileLoggingEnabled()
    {
        if (!$this->isLoggingEnabled()) {
            return false;
        }

        return $this->typeSuits('file');
    }

    protected function typeSuits($kind)
    {
        $logTypeConfig = $this->config['logging']['type'][$this->isDebug ? 'debug' : 'prod'];

        return in_array($logTypeConfig, ['both', $kind]);
    }

    public function isDatabaseLoggingEnabled()
    {
        if (!$this->isLoggingEnabled()) {
            return false;
        }

        return $this->typeSuits('database');
    }

    public function isLoggingEnabled()
    {
        $logEnv = $this->config['logging']['enabled'];
        if ($this->isDebug) {
            return in_array($logEnv, ['always', 'debug']);
        }

        return in_array($logEnv, ['always', 'prod']);
    }

    public function isMailSendingEnabled()
    {
        $mailEnv = $this->config['mails']['enabled'];
        if ($this->isDebug) {
            return in_array($mailEnv, ['always', 'debug']);
        }

        return in_array($mailEnv, ['always', 'prod']);
    }

    public function getMasterRequest()
    {
        return $this->requestStack->getMasterRequest();
    }

    protected function log($exception)
    {
        if (!$this->isLoggingEnabled()) {
            return;
        }
        $controller = $this->getMasterRequest()->attributes->get('_controller');
        $message = 'Exception was thrown.'."\n";
        $message.= '----------------------------------------------------------------------'."\n\n";
        $message.= 'Message: '.($exception instanceof \Throwable ? $exception->getMessage() : '404 error')."\n";
        $message.= 'Class: '.get_class($exception)."\n\n";
        $message.= 'Details: '."\n";
        $message.= '- controller: ' .($controller ?? '')."\n";
        $message.= '- redirect URL: '.(@$_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL'] : '')."\n";
        $message.= '- request URI: '.( @$_SERVER['REQUEST_URI']  ? $_SERVER['REQUEST_URI']  : '')."\n\n";
        $message.= 'Trace:'."\n";
        $message.='- '. ($exception instanceof \Throwable ? $exception->getTraceAsString() : '')."\n\n";
        $message.= '***********************************************************************'."\n\n";

        $this->logger->getLogger()->info($message);
    }

    public function isEnabled()
    {
        return $this->isMailSendingEnabled() || $this->isLoggingEnabled();
    }

    public function trigger($exception)
    {
        $error404 = $exception instanceof NotFoundHttpException;

        $mailSent = false;
        if (!$this->isEnabled())
        {
            return;
        }
        $enabled404 = !isset($this->config['mails']['send_404']) || $this->config['mails']['send_404'] !== false;
        if ($this->isMailSendingEnabled() && (!$error404 || $enabled404))
        {
            if (empty($this->config['mails']['send_only_if_new']) || !$this->isDatabaseLoggingEnabled())
            {
                $this->sendMail($exception);
                $mailSent = true;
            }
        }

        if ($this->isFileLoggingEnabled())
        {
            $this->log($exception);
        }

        if (!$this->isDatabaseLoggingEnabled())
        {
            return;
        }

        $sfNotify = new Notify();

        $sfNotify->setController($this->getMasterRequest()->attributes->get('_controller'));
        $sfNotify->setExceptionClass(get_class($exception));
        $sfNotify->setMessage($exception instanceof \Throwable ? $exception->getMessage() : '404 error');
        $sfNotify->setTraces($exception instanceof \Throwable ? $exception->getTraceAsString() : '');
        $sfNotify->setRedirectUrl(@$_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL'] : '' );
        $sfNotify->setRequestUri( @$_SERVER['REQUEST_URI']  ? $_SERVER['REQUEST_URI']  : '');
        $sfNotify->setServerName( @$_SERVER['HTTP_HOST']    ? $_SERVER['HTTP_HOST']    : '');
        $sfNotify->setPost(serialize(@$_POST));
        $sfNotify->setParams(serialize($this->requestStack->getCurrentRequest()->attributes->all()));
        $hash = $this->getHash($sfNotify);
        $sfNotify->setHash($hash);
        $sfNotify->setRequest(serialize(@$_REQUEST));

        $old = $this->doctrine->getRepository('HgabkaLoggerBundle:Notify')->findOneBy(['hash' => $hash]);

        if (!$old) {
            if (!$mailSent && (!$error404 || $enabled404)) {
                $this->sendMail($exception);
            }
        } else {
            $sfNotify = $old;
        }

        $called = is_null($sfNotify->getCallNumber()) ? 0 : $sfNotify->getCallNumber();
        $sfNotify->setCallNumber($called + 1);

        $em = $this->doctrine->getManager();
        $em->persist($sfNotify);

        $sfNotifyCall = new NotifyCall();
        $sfNotifyCall->setServer( serialize(@$_SERVER));
        $sfNotify->addCall($sfNotifyCall);

        $em->persist($sfNotifyCall);
        $em->flush();
    }

    protected function sendMail($exception)
    {
        $mailer = $this->mailer;
        $controller = $this->getMasterRequest()->attributes->get('_controller');

        $body =  'REDIRECT_URL:'.@$_SERVER['REDIRECT_URL'].'<br>';
        $body .= 'REQUEST_URI:'.@$_SERVER['REQUEST_URI'].'<br>';
        $body .= ($exception instanceof \Throwable ? $exception->getMessage() : '404 error').'<br>';
        $body .= ($exception instanceof \Throwable ? '<ul><li>'.implode('</li><li>', $this->getTraceArray($exception)).'</li></ul>' : '').'<br>';
        $body .= @$controller.'<br>';
        $body .= '<pre>';
        $body .= '<br>Paraméterek:<br>'.@var_export($this->requestStack->getCurrentRequest()->attributes->all(), true);
        $body .= '<br>SERVER:<br>'.@var_export(@$_SERVER, true);
        $fromName = isset($this->config['mails']['from_name']) ? $this->config['mails']['from_name'] : 'hgLoggerBundle';
        $fromEmail = isset($this->config['mails']['from_mail']) ? $this->config['mails']['from_mail'] : 'info@hgnotifier.com';

        $to = !isset($this->config['mails']['recipients']) ? 'hgabka@gmail.com' : $this->config['mails']['recipients'];
        $subject = isset($this->config['mails']['subject']) ? strtr($this->config['mails']['subject'],
            array('[host]' => $_SERVER['HTTP_HOST'],
                  '[redirect_url]' => @$_SERVER['REDIRECT_URL'],
                  '[request_uri]' => @$_SERVER['REQUEST_URI'] )) :
            'EXCEPTION on '.@$_SERVER['HTTP_HOST'].'!!! - '.@$_SERVER['REDIRECT_URL'].'-'.@$_SERVER['REQUEST_URI'];


        $mail = \Swift_Message::newInstance($subject);
        $mail->setFrom(array($fromEmail => $fromName));
        $mail->setTo($to);
        $mail->setBody($body, 'text/html');
        $mailer->send($mail);
    }

    protected function getTraceArray($exception)
    {
        if (!$exception instanceof \Throwable) {
            return [];
        }

        return explode("\n", $exception->getTraceAsString());
    }

    protected function getHash(Notify $notify)
    {
        return sha1(implode('|', get_object_vars($notify)));
    }
}
