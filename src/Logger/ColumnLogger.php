<?php

namespace Hgabka\LoggerBundle\Logger;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Gedmo\Tool\Wrapper\AbstractWrapper;
use Hgabka\LoggerBundle\Entity\LogColumn;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class ColumnLogger
{
    const OPT_USER = 'user';
    const OPT_ORIGINAL_USER = 'original_user';
    const OPT_URL = 'url';
    const OPT_IP = 'ip';
    const OPT_SESSION = 'session';
    const OPT_USER_AGENT = 'user_agent';
    const OPT_ACTION = 'action';

    const MOD_TYPE_INSERT = 'INSERT';
    const MOD_TYPE_UPDATE = 'UPDATE';
    const MOD_TYPE_DELETE = 'DELETE';

    /** @var Registry */
    protected $doctrine;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var string */
    protected $ident;

    /** @var AuthorizationCheckerInterface */
    protected $authChecker;

    /** @var RequestStack */
    protected $requestStack;

    /** @var Session */
    protected $session;

    /**
     * ColumnLogger constructor.
     *
     * @param Registry              $doctrine
     * @param TokenStorageInterface $tokenStorage
     * @param string                $ident
     */
    public function __construct(Registry $doctrine, TokenStorageInterface $tokenStorage, RequestStack $requestStack, Session $session, AuthorizationCheckerInterface $authChecker, string $ident)
    {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->ident = $ident;
        $this->authChecker = $authChecker;
        $this->requestStack = $requestStack;
        $this->session = $session;
    }

    /**
     * Egy model mezőinek változásának logolása.
     *
     * @param $obj
     * @param string        $action     LogColumnPeer::MOD_TYPE_* konstansok
     * @param EntityManager $em
     * @param array         $changeData
     *
     * @return array
     */
    public function logColumns($obj, $action, EntityManager $em, array $changeData = null)
    {
        $request = $this->requestStack->getCurrentRequest();
        $context = $this->getContextOptions();
        $objClass = get_class($obj);
        $metaData = $em->getClassMetadata($objClass);

        $table = $metaData->getTableName();

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

        $userId = $user && is_object($user) ? $user->getId() : null;
        $originalUserId = $originalUser ? $originalUser->getId() : null;

        $isDelete = self::MOD_TYPE_DELETE === $action;
        $wrapped = AbstractWrapper::wrap($obj, $em);
        $fk = $wrapped->getIdentifier(false);

        if (is_array($fk)) {
            $fk = implode('#', $fk);
        }

        if (empty($fk)) {
            $fk = null;
        }

        $entityData = [];
        foreach ($metaData->getColumnNames() as $field) {
            $entityData[$field] = $metaData->getFieldValue($obj, $metaData->getFieldForColumn($field));
        }

        $logs = [];
        if ($isDelete) {
            $log = new LogColumn();
            $log
                ->setIdent($this->ident)
                ->setTable($table)
                ->setClass($objClass)
                ->setForeignId($fk)
                ->setUserId($userId)
                ->setOriginalUserId($originalUserId)
                ->setModType($action)
                ->setData(json_encode($entityData, JSON_UNESCAPED_UNICODE))
                ->setPost($request ? \json_encode($request->request->all()) : null)
                ->setRequestAttributes($request ? \json_encode($request->attributes->all()) : null)
                ->setMethod($request ? ($request->getMethod().' ('.$request->getRealMethod().')') : null)
                ->setClientIp($context[self::OPT_IP])
                ->setController($context[self::OPT_ACTION])
                ->setSessionId($context[self::OPT_SESSION])
                ->setUserAgent($context[self::OPT_USER_AGENT])
                ->setUserId($context[self::OPT_USER])
                ->setOriginalUserId($context[self::OPT_ORIGINAL_USER])
                ->setRequestUri($context[self::OPT_URL])
            ;
            if ($originalUser) {
                $log->setNote('Impersonated (original user: '.$extraParameters.')');
            }
            $logs[] = $log;
        } else {
            $logFields = $obj->getLogFields();

            foreach ($changeData as $field => $changeData) {
                if (!is_array($logFields) || (!empty($logFields) && !in_array($field, $logFields, true))) {
                    continue;
                }

                if (empty($logFields) && in_array($field, ['createdAt', 'updatedAt'], true)) {
                    continue;
                }

                $fieldName = $metaData->getColumnName($field);
                $oldVal = self::MOD_TYPE_INSERT === $action ? null : $changeData[0];
                $newVal = $changeData[1];

                if (($oldValue = $this->convertValue($oldVal)) === ($newValue = $this->convertValue($newVal))) {
                    continue;
                }

                $log = new LogColumn();
                $log
                    ->setIdent($this->ident)
                    ->setTable($table)
                    ->setClass($objClass)
                    ->setForeignId($fk)
                    ->setColumn($fieldName)
                    ->setField($field)
                    ->setOldValue($oldValue)
                    ->setNewValue($newValue)
                    ->setUserId($userId)
                    ->setOriginalUserId($originalUserId)
                    ->setModType($action)
                    ->setData(json_encode($entityData, JSON_UNESCAPED_UNICODE))
                    ->setPost($request ? \json_encode($request->request->all()) : null)
                    ->setRequestAttributes($request ? \json_encode($request->attributes->all()) : null)
                    ->setMethod($request ? ($request->getMethod().' ('.$request->getRealMethod().')') : null)
                    ->setClientIp($context[self::OPT_IP])
                    ->setController($context[self::OPT_ACTION])
                    ->setSessionId($context[self::OPT_SESSION])
                    ->setUserAgent($context[self::OPT_USER_AGENT])
                    ->setUserId($context[self::OPT_USER])
                    ->setOriginalUserId($context[self::OPT_ORIGINAL_USER])
                    ->setRequestUri($context[self::OPT_URL])
                ;
                if ($originalUser) {
                    $log->setNote('Impersonated (original user: '.$extraParameters.')');
                }
                $logs[] = $log;
            }
        }

        return $logs;
    }

    protected function convertValue($value)
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
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
