<?php

namespace Hgabka\LoggerBundle\Logger;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Gedmo\Tool\Wrapper\AbstractWrapper;
use Hgabka\LoggerBundle\Entity\LogColumn;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ColumnLogger extends AbstractLogger
{
    const MOD_TYPE_INSERT = 'INSERT';
    const MOD_TYPE_UPDATE = 'UPDATE';
    const MOD_TYPE_DELETE = 'DELETE';

    /**
     * ColumnLogger constructor.
     *
     * @param ManagerRegistry       $doctrine
     * @param TokenStorageInterface $tokenStorage
     * @param string                $ident
     */
    public function __construct(ManagerRegistry $doctrine, TokenStorageInterface $tokenStorage, RequestStack $requestStack, Session $session, AuthorizationCheckerInterface $authChecker, bool $debug, string $ident, string $enabled)
    {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->ident = $ident;
        $this->authChecker = $authChecker;
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->debug = $debug;
        $this->enabled = $enabled;
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
        if (!$this->isLoggingEnabled()) {
            return [];
        }

        ini_set('memory_limit', '-1');

        $metaData = $em->getClassMetadata(\get_class($obj));
        $objClass = $metaData->getName();

        $table = $metaData->getTableName();

        $isDelete = self::MOD_TYPE_DELETE === $action;
        $wrapped = AbstractWrapper::wrap($obj, $em);
        $fk = $wrapped->getIdentifier(false);

        if (\is_array($fk)) {
            $fk = implode('#', $fk);
        }

        if (empty($fk)) {
            $fk = null;
        }

        $entityData = [];
        foreach ($metaData->getColumnNames() as $field) {
            $entityData[$field] = $metaData->getFieldValue($obj, $metaData->getFieldForColumn($field));
        }
        $context = $this->getContextOptions();
        $originalUser = $context[static::OPT_ORIGINAL_USER_OBJECT];

        $logs = [];
        if ($isDelete) {
            $log = new LogColumn();
            $log
                ->setTable($table)
                ->setClass($objClass)
                ->setForeignId($fk)
                ->setModType($action)
                ->setData(json_encode($entityData, JSON_UNESCAPED_UNICODE))
            ;
            $this->setLogFields($log);
            if ($originalUser && \is_object()) {
                $log->setNote('Impersonated (original user: '.$context[self::OPT_ORIGINAL_USERNAME].')');
            }
            $logs[] = $log;
        } else {
            $logFields = $obj->getLogFields();

            foreach ($changeData as $field => $changeData) {
                if (!\is_array($logFields) || (!empty($logFields) && !\in_array($field, $logFields, true))) {
                    continue;
                }

                if (empty($logFields) && \in_array($field, ['createdAt', 'updatedAt'], true)) {
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
                    ->setTable($table)
                    ->setClass($objClass)
                    ->setForeignId($fk)
                    ->setColumn($fieldName)
                    ->setField($field)
                    ->setOldValue($oldValue)
                    ->setNewValue($newValue)
                    ->setModType($action)
                    ->setData(json_encode($entityData, JSON_UNESCAPED_UNICODE))
                ;
                $this->setLogFields($log);

                if ($originalUser) {
                    $log->setNote('Impersonated (original user: '.$context[static::OPT_ORIGINAL_USERNAME].')');
                }
                $logs[] = $log;
            }
        }

        return $logs;
    }

    protected function setLogFields($log)
    {
        $request = $this->requestStack->getCurrentRequest();
        $context = $this->getContextOptions();

        $log
            ->setIdent($this->ident)
            ->setPost($request ? json_encode($request->request->all()) : null)
            ->setRequestAttributes($request ? json_encode($request->attributes->all()) : null)
            ->setMethod($request ? ($request->getMethod().' ('.$request->getRealMethod().')') : null)
            ->setClientIp($context[static::OPT_IP])
            ->setController($context[static::OPT_ACTION])
            ->setSessionId($context[static::OPT_SESSION])
            ->setUserAgent($context[static::OPT_USER_AGENT])
            ->setUserId($context[static::OPT_USER])
            ->setOriginalUserId($context[static::OPT_ORIGINAL_USER])
            ->setUsername($context[static::OPT_USERNAME])
            ->setOriginalUsername($context[static::OPT_ORIGINAL_USERNAME])
            ->setRequestUri($context[static::OPT_URL])
        ;
    }

    protected function convertValue($value)
    {
        if (\is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (\is_array($value) || \is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }
}
