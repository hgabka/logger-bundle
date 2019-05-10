<?php

namespace Hgabka\LoggerBundle\Logger;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Hgabka\LoggerBundle\Entity\LogAction;
use Hgabka\LoggerBundle\Event\LogActionEvent;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Translation\TranslatorInterface;

class ActionLogger
{
    const OPT_USER = 'user';
    const OPT_ORIGINAL_USER = 'original_user';
    const OPT_URL = 'url';
    const OPT_IP = 'ip';
    const OPT_SESSION = 'session';
    const OPT_USER_AGENT = 'user_agent';
    const OPT_ACTION = 'action';

    /** @var Registry */
    protected $doctrine;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var string */
    protected $ident;

    /** @var string */
    protected $catalog;

    /**
     * A start()-al elkezdett log.
     *
     * @var LogAction
     */
    protected $startedObj;

    /** @var RequestStack */
    protected $requestStack;

    /** @var Session */
    protected $session;

    /** @var AuthorizationCheckerInterface */
    protected $authChecker;

    /**
     * ColumnLogger constructor.
     *
     * @param Registry              $doctrine
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface   $translator
     * @param RequestStack          $requestStack
     * @param Session               $session
     * @param string                $ident
     * @param string                $catalog
     */
    public function __construct(Registry $doctrine, TokenStorageInterface $tokenStorage, TranslatorInterface $translator, RequestStack $requestStack, Session $session, AuthorizationCheckerInterface $authChecker, string $ident, string $catalog)
    {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->ident = $ident;
        $this->catalog = $catalog;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->authChecker = $authChecker;
    }

    /**
     * Egy esemény kezdetének logolása.
     *
     * @param string       $type
     * @param array|string $i18nParamsOrMessage String esetén ez lesz a szöveg
     * @param null|mixed   $extraParameters
     *
     * @return ActionLogger
     */
    public function start($type, $i18nParamsOrMessage = null, $priority = null, $extraParameters = null)
    {
        $this->startedObj = $this->logAction(LogActionEvent::EVENT_START, $type, $i18nParamsOrMessage, $priority, $extraParameters);

        return $this;
    }

    /**
     * Az elkezdett esemény log adatainak frissítése
     * Mindenképp meg kell hívni előtte a start()-ot.
     *
     * @param array|string $i18nParamsOrMessage String esetén ez lesz a szöveg
     * @param null|mixed   $priority
     * @param null|mixed   $extraParameters
     *
     * @return ActionLogger
     */
    public function update($i18nParamsOrMessage = null, $priority = null, $extraParameters = null)
    {
        if (null === $this->startedObj) {
            throw new \LogicException('Az update() meghivasa elott meg kell hivni a start()-ot');
        }

        if (null !== $i18nParamsOrMessage) {
            $i18nParamsOrMessage = $this->startedObj->getDescription();
        }
        if (null !== $priority) {
            $this->startedObj->setPriority($priority);
        }
        if (null !== $extraParameters) {
            $this->startedObj->setExtraParameters($extraParameters ?? null);
        }

        $this->logAction(LogActionEvent::EVENT_UPDATE, $this->startedObj->getType(), $i18nParamsOrMessage, $priority, $extraParameters);

        return $this;
    }

    /**
     * Az elkezdett esemény logolásának befejezése
     * Mindenképp meg kell hívni előtte a start()-ot.
     *
     * @return ActionLogger
     */
    public function done()
    {
        if (null === $this->startedObj) {
            throw new \LogicException('A done() meghivasa elott meg kell hivni a start()-ot');
        }

        $this->logAction(LogActionEvent::EVENT_DONE, null);
        $this->startedObj = null;

        return $this;
    }

    /**
     * Egy esemény azonnali logolása.
     *
     * @param string       $type
     * @param array|string $i18nParamsOrMessage String esetén ez lesz a szöveg
     * @param int          $priority            sfLogger konstansok
     * @param null|mixed   $extraParameters
     *
     * @return ActionLogger
     */
    public function log($type, $i18nParamsOrMessage = null, $priority = null, $extraParameters = null)
    {
        $this->logAction(LogActionEvent::EVENT_LOG, $type, $i18nParamsOrMessage, $priority, $extraParameters);

        return $this;
    }

    /**
     * Logolás meghívás.
     *
     * @param string     $kind
     * @param string     $type
     * @param array      $i18nParamsOrMessage
     * @param int        $priority            sfLogger konstansok
     * @param null|mixed $extraParameters
     *
     * @return null|LogAction
     */
    protected function logAction($kind, $type, $i18nParamsOrMessage = [], $priority = null, $extraParameters = null)
    {
        $em = $this->doctrine->getManager();
        $priority = $priority ? $priority : Logger::getLevelName(Logger::INFO);
        $context = $this->getContextOptions();
        if ($context[self::OPT_ORIGINAL_USER]) {
            if (empty($extraParameters)) {
                $extraParameters = '';
            }
            $extraParameters .= ((empty($extraParameters) ? "\n" : '').'Impersonated (original user: '.$context[self::OPT_ORIGINAL_USER].')');
        }

        switch ($kind) {
            case LogActionEvent::EVENT_UPDATE:
            case LogActionEvent::EVENT_LOG:
            case LogActionEvent::EVENT_START:

                $request = $this->requestStack->getCurrentRequest();
                if (LogActionEvent::EVENT_UPDATE === $kind) {
                    $obj = $this->startedObj;
                } else {
                    $obj = new LogAction();
                    $obj
                        ->setIdent($this->ident)
                        ->setTime(new \DateTime())
                        ->setType($type)
                        ->setPriority($priority)
                        ->setPost($request ? \json_encode($request->request->all()) : null)
                        ->setRequestAttributes($request ? \json_encode($request->attributes->all()) : null)
                        ->setMethod($request ? ($request->getMethod().' ('.$request->getRealMethod().')') : null)
                        ->setSuccess(false)
                        ->setExtraParameters($extraParameters ?? null)
                    ;
                }

                $obj
                    ->setClientIp($context[self::OPT_IP])
                    ->setController($context[self::OPT_ACTION])
                    ->setSessionId($context[self::OPT_SESSION])
                    ->setUserAgent($context[self::OPT_USER_AGENT])
                    ->setUserId($context[self::OPT_USER])
                    ->setOriginalUserId($context[self::OPT_ORIGINAL_USER])
                    ->setRequestUri($context[self::OPT_URL])
                    ->setExtraParameters($extraParameters ?? null)
                ;

                if (is_string($i18nParamsOrMessage)) {
                    $obj->setDescription($i18nParamsOrMessage);
                } else {
                    if (LogActionEvent::EVENT_UPDATE === $kind && null === $i18nParamsOrMessage) {
                        // update ne irja felül a szöveget, ha nincs megadva paraméter
                    } else {
                        // type fordítása a paraméterek segítségével
                        $obj->setDescription($this->translator->trans($type, $i18nParamsOrMessage, $this->catalog));
                    }
                }

                break;
            case LogActionEvent::EVENT_DONE:
                if ($this->startedObj) {
                    $this->startedObj
                        ->setEndTime(new \DateTime())
                        ->setSuccess($this->startedObj->getPriority() !== Logger::getLevelName(Logger::ERROR))
                    ;
                    $em->persist($this->startedObj);
                    $em->flush($this->startedObj);
                }
                $obj = null;

                break;
            default:
                throw new \LogicException('Érvénytelen log fajta: '.$kind);
        }

        if ($obj) {
            $em->persist($obj);
            $em->flush($obj);
        }

        return $obj;
    }

    /**
     * Kontextus információk, minden ami globálisan elérhető.
     *
     * @return array
     */
    protected function getContextOptions()
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        $originalUser = null;
        if ($this->tokenStorage->getToken() && $this->authChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            foreach ($this->tokenStorage->getToken()->getRoles() as $role) {
                if ($role instanceof SwitchUserRole) {
                    $originalUser = $role->getSource()->getUser();

                    break;
                }
            }
        }
        $this->session->start();
        $session = $this->session->getId();

        return [
            self::OPT_USER => $user && is_object($user) ? $user->getId() : null,
            self::OPT_ORIGINAL_USER => $originalUser && is_object($originalUser) ? $originalUser->getId() : null,
            self::OPT_USER_AGENT => $request ? $request->headers->get('User-Agent') : null,
            self::OPT_IP => $request ? $request->getClientIp() : null,
            self::OPT_URL => $request ? $request->getRequestUri() : null,
            self::OPT_SESSION => $session ? $session : null,
            self::OPT_ACTION => $request ? $request->attributes->get('_controller') : null,
        ];
    }
}
