<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Hgabka\LoggerBundle\Logger;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Gedmo\Tool\Wrapper\AbstractWrapper;
use Hgabka\LoggerBundle\Entity\LogColumn;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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

    /**
     * ColumnLogger constructor.
     *
     * @param Registry              $doctrine
     * @param TokenStorageInterface $tokenStorage
     * @param string                $ident
     */
    public function __construct(Registry $doctrine, TokenStorageInterface $tokenStorage, string $ident)
    {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->ident = $ident;
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

        $user = $this->tokenStorage->getToken()->getUser();
        $userId = $user && is_object($user) ? $user->getId() : null;

        $isDelete = $action === self::MOD_TYPE_DELETE;
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
                ->setModType($action)
                ->setData(json_encode($entityData))
            ;
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
                $oldVal = $action == self::MOD_TYPE_INSERT ? null : $changeData[0];
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
                    ->setModType($action)
                    ->setData(json_encode($entityData))
                ;
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
            return json_encode($value);
        }

        return (string) $value;
    }
}
