<?php

namespace Hgabka\LoggerBundle\Logger;

use Doctrine\Common\Persistence\ManagerRegistry;
use Gedmo\Tool\Wrapper\AbstractWrapper;
use Hgabka\LoggerBundle\Entity\ObjectLogInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;

class AbstractLogger
{
    const OPT_USER = 'user';
    const OPT_ORIGINAL_USER = 'original_user';
    const OPT_URL = 'url';
    const OPT_IP = 'ip';
    const OPT_SESSION = 'session';
    const OPT_USER_AGENT = 'user_agent';
    const OPT_ACTION = 'action';

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var string */
    protected $ident;

    /** @var RequestStack */
    protected $requestStack;

    /** @var Session */
    protected $session;

    /** @var AuthorizationCheckerInterface */
    protected $authChecker;

    /** @var string */
    protected $enabled;

    /** @var bool */
    protected $debug;

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
            static::OPT_USER => $user && \is_object($user) ? $user->getId() : null,
            static::OPT_ORIGINAL_USER => $originalUser && \is_object($originalUser) ? $originalUser->getId() : null,
            static::OPT_USER_AGENT => $request ? $request->headers->get('User-Agent') : null,
            static::OPT_IP => $request ? $request->getClientIp() : null,
            static::OPT_URL => $request ? $request->getRequestUri() : null,
            static::OPT_SESSION => $session ? $session : null,
            static::OPT_ACTION => $request ? $request->attributes->get('_controller') : null,
        ];
    }

    protected function setObject(ObjectLogInterface $log, $object)
    {
        $em = $this->doctrine->getManager();
        if (\is_object($object)) {
            $objClass = \get_class($object);
            $metaData = $em->getClassMetadata($objClass);

            $fk = null;
            $table = null;

            if ($metaData) {
                $table = $metaData->getTableName();
                $wrapped = AbstractWrapper::wrap($object, $em);
                $fk = $wrapped->getIdentifier(false);

                if (\is_array($fk)) {
                    $fk = implode('#', $fk);
                }

                if (empty($fk)) {
                    $fk = null;
                }
            }
        } else {
            $objClass = null;
            $table = null;
            $fk = null;
        }

        $log
            ->setTable($table)
            ->setClass($objClass)
            ->setForeignId($fk)
        ;
    }

    protected function isLoggingEnabled()
    {
        $logEnv = $this->enabled;
        if ($this->debug) {
            return \in_array($logEnv, ['always', 'debug'], true);
        }

        return \in_array($logEnv, ['always', 'prod'], true);
    }
}
