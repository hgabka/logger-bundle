<?php

namespace Hgabka\LoggerBundle\Logger;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Gedmo\Tool\Wrapper\AbstractWrapper;
use Hgabka\LoggerBundle\Entity\LogColumn;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ColumnLogger
{
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

    /**
     * ColumnLogger constructor.
     *
     * @param Registry              $doctrine
     * @param TokenStorageInterface $tokenStorage
     * @param string                $ident
     */
    public function __construct(Registry $doctrine, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authChecker, string $ident)
    {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->ident = $ident;
        $this->authChecker = $authChecker;
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
                $log = new LogColumn();
                $log
                    ->setIdent($this->ident)
                    ->setTable($table)
                    ->setClass($objClass)
                    ->setForeignId($fk)
                    ->setColumn($fieldName)
                    ->setField($field)
                    ->setOldValue($this->convertValue($oldVal))
                    ->setNewValue($this->convertValue($newVal))
                    ->setUserId($userId)
                    ->setOriginalUserId($originalUserId)
                    ->setModType($action)
                    ->setData(json_encode($entityData, JSON_UNESCAPED_UNICODE))
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
}
