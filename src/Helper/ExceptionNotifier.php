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

    /** @var array */
    protected $config;

    /** @var bool */
    protected $isDebug;

    /**
     * ExceptionNotifier constructor.
     *
     * @param Registry        $doctrine
     * @param \Swift_Mailer   $mailer
     * @param RequestStack    $requestStack
     * @param ExceptionLogger $logger
     * @param bool            $isDebug
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
     *
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
            return \in_array($logEnv, ['always', 'debug'], true);
        }

        return \in_array($logEnv, ['always', 'prod'], true);
    }

    public function isMailSendingEnabled()
    {
        $mailEnv = $this->config['mails']['enabled'];
        if ($this->isDebug) {
            return \in_array($mailEnv, ['always', 'debug'], true);
        }

        return \in_array($mailEnv, ['always', 'prod'], true);
    }

    public function getMasterRequest()
    {
        return $this->requestStack->getMasterRequest();
    }

    public function isEnabled()
    {
        return $this->isMailSendingEnabled() || $this->isLoggingEnabled();
    }

    public function trigger($exception)
    {
        $error404 = $exception instanceof NotFoundHttpException;

        $mailSent = false;
        if (!$this->isEnabled()) {
            return;
        }
        $enabled404 = !isset($this->config['mails']['send_404']) || false !== $this->config['mails']['send_404'];
        if (!$error404 || $enabled404) {
            if (empty($this->config['mails']['send_only_if_new']) || !$this->isDatabaseLoggingEnabled()) {
                $this->sendMail($exception);
                $mailSent = true;
            }
        }
        $enabledLog404 = !isset($this->config['logging']['log_404']) || false !== $this->config['logging']['log_404'];

        if ($error404 && !$enabledLog404) {
            return;
        }
        if ($this->isFileLoggingEnabled()) {
            $this->log($exception);
        }

        if (!$this->isDatabaseLoggingEnabled()) {
            return;
        }

        $sfNotify = new Notify();
        $controller = $this->getMasterRequest() && $this->getMasterRequest()->attributes ? $this->getMasterRequest()->attributes->get('_controller') : '';

        $sfNotify->setController($controller);
        $sfNotify->setExceptionClass(\get_class($exception));
        $sfNotify->setMessage($exception instanceof \Throwable ? $exception->getMessage() : '404 error');
        $sfNotify->setTraces($exception instanceof \Throwable ? $exception->getTraceAsString() : '');
        $sfNotify->setRedirectUrl(@$_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL'] : '');
        $sfNotify->setRequestUri(@$_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : '');
        $sfNotify->setServerName(@$_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : '');
        $sfNotify->setPost(json_encode(@$_POST));
        $sfNotify->setParams(json_encode($_GET));
        $sfNotify->setCode($exception->getCode());
        $sfNotify->setLine($exception->getLine());
        $sfNotify->setFile($exception->getFile());
        $hash = $this->getHash($sfNotify);
        $sfNotify->setHash($hash);
        $sfNotify->setRequest(json_encode(@$_REQUEST));

        $old = $this->doctrine->getRepository(Notify::class)->findOneBy(['hash' => $hash]);

        if (!$old) {
            if (!$mailSent && (!$error404 || $enabled404)) {
                $this->sendMail($exception);
            }
        } else {
            $sfNotify = $old;
        }

        $called = null === $sfNotify->getCallNumber() ? 0 : $sfNotify->getCallNumber();
        $sfNotify->setCallNumber($called + 1);

        $em = $this->doctrine->getManager();

        if ($em->isOpen()) {
            $em->persist($sfNotify);

            $sfNotifyCall = new NotifyCall();
            $sfNotifyCall->setServer(json_encode(@$_SERVER));
            $sfNotify->addCall($sfNotifyCall);

            $em->persist($sfNotifyCall);
            $em->flush($sfNotify);
            $em->flush($sfNotifyCall);
        }
    }

    protected function typeSuits($kind)
    {
        $logTypeConfig = $this->config['logging']['type'][$this->isDebug ? 'debug' : 'prod'];

        return \in_array($logTypeConfig, ['both', $kind], true);
    }

    protected function log($exception)
    {
        if (!$this->isLoggingEnabled()) {
            return;
        }
        $controller = $this->getMasterRequest() && $this->getMasterRequest()->attributes ? $this->getMasterRequest()->attributes->get('_controller') : '';

        $message = 'Exception was thrown.'."\n";
        $message .= '----------------------------------------------------------------------'."\n\n";
        $message .= 'Message: '.($exception instanceof \Throwable ? $exception->getMessage() : '404 error')."\n";
        $message .= 'File: '.$exception->getFile()."\n";
        $message .= 'Line: '.$exception->getLine()."\n";
        $message .= 'Code: '.$exception->getCode()."\n";
        $message .= 'Class: '.\get_class($exception)."\n\n";
        $message .= 'Details: '."\n";
        $message .= '- controller: '.($controller ?? '')."\n";
        $message .= '- redirect URL: '.(@$_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL'] : '')."\n";
        $message .= '- request URI: '.(@$_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : '')."\n\n";
        $message .= 'Trace:'."\n";
        $message .= '- '.($exception instanceof \Throwable ? $exception->getTraceAsString() : '')."\n\n";
        $message .= '***********************************************************************'."\n\n";

        $this->logger->getLogger()->info($message);
    }

    protected function sendMail($exception)
    {
        if (!$this->isMailSendingEnabled()) {
            return;
        }

        $mailer = $this->mailer;
        $controller = $this->getMasterRequest() && $this->getMasterRequest()->attributes ? $this->getMasterRequest()->attributes->get('_controller') : '';
        
        $message = ($exception instanceof \Throwable ? $exception->getMessage() : '404 error');
        $width = 1200;

        $body = '
        <!DOCTYPE html>
        <html style="width:'.$width.'px">
            <head>
                <meta charset="UTF-8" />
                <title>'.$message.'</title>
            </head>   
            <body style="width:'.$width.'px">
            
        <pre width="'.$width.'" style="max-width:'.$width.'px;word-wrap: break-word;overflow-wrap: break-word;hyphens: auto;white-space: pre-wrap;">';
        $body .= 'REDIRECT_URL:'.@$_SERVER['REDIRECT_URL'].'<br>';
        $body .= 'REQUEST_URI:'.@$_SERVER['REQUEST_URI'].'<br>';
        $body .= ('<br />Exception message: <br /><br /><p style="font-size:18px;font-weight:bold;display:block;max-width:100%;word-wrap: break-word;overflow-wrap: break-word;hyphens: auto;">'.$message.'</p><br />').'<br>';
        $body .= 'File: '.$exception->getFile().'<br />';
        $body .= 'Line: '.$exception->getLine().'<br />';
        $body .= 'Code: '.$exception->getCode().'<br />';
        $body .= 'Class: '.\get_class($exception).'<br /><br />';
        $body .= ($exception instanceof \Throwable ? '<ul><li>'.implode('</li><li>', $this->getTraceArray($exception)).'</li></ul>' : '').'<br>';
        $body .= ('Controller: '.$controller.'<br>');

        $req = $this->requestStack->getCurrentRequest();
        if ($req) {
            $pars = array_merge($req->request->all(), $req->query->all());
            foreach ($pars as $key => $data) {
                if (\is_object($data)) {
                    unset($pars[$key]);
                }
            }
        } else {
            $pars = $_REQUEST;
        }
        $body .= ('<br>Paraméterek:<br>'.var_export($pars, true));

        $body .= '<br>SERVER:<br>'.var_export(@$_SERVER, true);
        $body .= '</pre></body></html>';

        $fromName = isset($this->config['mails']['from_name']) ? $this->config['mails']['from_name'] : 'hgLoggerBundle';
        $fromEmail = isset($this->config['mails']['from_mail']) ? $this->config['mails']['from_mail'] : 'info@hgnotifier.com';

        $to = !isset($this->config['mails']['recipients']) ? 'hgabka@gmail.com' : $this->config['mails']['recipients'];
        $subject = isset($this->config['mails']['subject']) ? strtr(
            $this->config['mails']['subject'],
            ['[host]' => $_SERVER['HTTP_HOST'],
                  '[redirect_url]' => @$_SERVER['REDIRECT_URL'],
                  '[request_uri]' => @$_SERVER['REQUEST_URI'], ]
        ) :
            'EXCEPTION on '.@$_SERVER['HTTP_HOST'].'!!! - '.@$_SERVER['REDIRECT_URL'].'-'.@$_SERVER['REQUEST_URI'];

        $mail = new \Swift_Message($subject);
        $mail->setFrom([$fromEmail => $fromName]);
        $mail->setTo($to);
        $mail->setBody($body, 'text/html');
        $mail->setReturnPath('hgabka@gmail.com');
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
        return sha1(implode('|', $this->entityToArray($notify)));
    }

    protected function entityToArray($entity)
    {
        if (empty($entity)) {
            return [];
        }
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $md = $em->getClassMetadata(\get_class($entity));

        $result = [];
        if ($md) {
            foreach ($md->getFieldNames() as $field) {
                $result[$field] = $md->getFieldValue($entity, $field);
            }
        }

        return $result;
    }
}
