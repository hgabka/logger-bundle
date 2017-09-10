<?php
/**
 * Created by PhpStorm.
 * User: sfhun
 * Date: 2017.09.10.
 * Time: 15:29
 */

namespace Hgabka\LoggerBundle\Logger;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Mapping\ClassMetadata;
use Hgabka\LoggerBundle\Entity\LogColumn;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ColumnLogger
{
    const MOD_TYPE_INSERT = 'INSERT';
    const MOD_TYPE_UPDATE = 'UPDATE';
    const MOD_TYPE_DELETE = 'DELETE';

    /** @var  Registry */
    protected $doctrine;

    /** @var  TokenStorageInterface */
    protected $tokenStorage;

    /** @var  string */
    protected $ident;

    /**
     * ColumnLogger constructor.
     * @param Registry $doctrine
     * @param TokenStorageInterface $tokenStorage
     * @param string $ident
     */
    public function __construct(Registry $doctrine, TokenStorageInterface $tokenStorage, string $ident)
    {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->ident = $ident;
    }

    /**
     * Egy model mezőinek változásának logolása
     * @param $obj
     * @param string $action LogColumnPeer::MOD_TYPE_* konstansok
     * @param ClassMetadata $metaData
     * @param array $changeData
     * @return array
     */
    public function logColumns($obj, $action, ClassMetadata $metaData, array $changeData = null)
    {
        $objClass = get_class($obj);

        $table = $metaData->getTableName();

        $user = $this->tokenStorage->getToken()->getUser();
        $userId = $user ? $user->getId() : null;

        $isDelete = $action == self::MOD_TYPE_DELETE;
        $fk = (string)($obj->{'get'.$metaData->getSingleIdentifierFieldName()}());
        if (empty($fk)) {
            $fk = null;
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
            ;
            $logs[] = $log;
        } else {
            $logFields = $obj->getLogFields();

            foreach ($changeData as $field => $changeData) {
                if (!is_array($logFields) || (!empty($logFields) && !in_array($field, $logFields))) {
                    continue;
                }

                if (empty($logFields) && in_array($field, ['createdAt', 'updatedAt'])) {
                    continue;
                }

                $fieldName = $metaData->getColumnName($field);
                $oldVal = $changeData[0];
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

        return (string)$value;
    }
}
