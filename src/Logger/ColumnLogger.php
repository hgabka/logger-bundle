<?php

namespace Hgabka\LoggerBundle\Logger;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Gedmo\Tool\Wrapper\AbstractWrapper;
use Hgabka\LoggerBundle\Entity\LogColumn;
use Hgabka\LoggerBundle\Helper\LoggableEntityInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ColumnLogger extends AbstractLogger
{
    public const MOD_TYPE_INSERT = 'INSERT';
    public const MOD_TYPE_UPDATE = 'UPDATE';
    public const MOD_TYPE_DELETE = 'DELETE';

    /** @var Registry */
    protected $doctrine;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var RequestStack */
    protected $requestStack;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authChecker;

    /** @var string */
    protected $ident;

    /** @var string */
    protected $enabled;

    /** @var bool */
    protected $debug;

    /**
     * ColumnLogger constructor.
     *
     * @param ManagerRegistry       $doctrine
     * @param TokenStorageInterface $tokenStorage
     * @param string                $ident
     */
    public function __construct(Registry $doctrine, TokenStorageInterface $tokenStorage, TranslatorInterface $translator, RequestStack $requestStack, AuthorizationCheckerInterface $authChecker, bool $debug, string $ident, string $enabled)
    {
        $this->doctrine = $doctrine;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->authChecker = $authChecker;
        $this->enabled = $enabled;
        $this->ident = $ident;
        $this->debug = $debug;
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
    public function logColumns(LoggableEntityInterface $obj, $action, EntityManager $em, array $changeData = null)
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
                ->setData(json_encode($entityData, \JSON_UNESCAPED_UNICODE))
            ;
            $this->setLogFields($log);
            if ($originalUser && \is_object()) {
                $log->setNote('Impersonated (original user: ' . $context[self::OPT_ORIGINAL_USERNAME] . ')');
            }
            $logs[] = $log;
        } else {
            $logFields = $obj->getLogFields();
            $notLogFields = $obj->getNotLogFields();

            foreach ($changeData as $field => $changeData) {
                if (null !== $notLogFields && \in_array($field, $notLogFields, true)) {
                    continue;
                }

                if (!\is_array($logFields) || (!empty($logFields) && !\in_array($field, $logFields, true))) {
                    continue;
                }

                if (empty($logFields) && \in_array($field, ['createdAt', 'updatedAt'], true)) {
                    continue;
                }

                $fieldName = $metaData->getColumnName($field);
                $oldVal = self::MOD_TYPE_INSERT === $action ? null : $changeData[0];
                $newVal = $changeData[1];

                $mapping = $metaData->getFieldMapping($field);
                $type = $mapping['type'] ?? null;

                if (($oldValue = $this->convertValue($oldVal, $type)) === ($newValue = $this->convertValue($newVal, $type))) {
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
                    ->setData(json_encode($entityData, \JSON_UNESCAPED_UNICODE))
                ;
                $this->setLogFields($log);

                if ($originalUser) {
                    $log->setNote('Impersonated (original user: ' . $context[static::OPT_ORIGINAL_USERNAME] . ')');
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
            ->setMethod($request ? ($request->getMethod() . ' (' . $request->getRealMethod() . ')') : null)
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

    protected function convertValue($value, $type)
    {
        $origValue = $value;

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('datetime' === $type ? 'Y-m-d H:i:s' : 'Y-m-d H:i');
        }

        if (\is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (\is_array($value) || \is_object($value)) {
            $value = json_encode($value, \JSON_UNESCAPED_UNICODE);
        }

        if ('boolean' === $type) {
            return $this->translator->trans('hgabka_logger.log_value.' . ((bool) $value ? 'yes' : 'no'), [], 'HgabkaLoggerBundle');
        }

        if ('integer' === $type) {
            return (int) $value;
        }

        return (string) $value;
    }
}
