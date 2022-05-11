<?php

namespace Hgabka\LoggerBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Hgabka\LoggerBundle\Helper\LoggableEntityInterface;
use Hgabka\LoggerBundle\Logger\ColumnLogger;

class ColumnLogListener
{
    protected ColumnLogger $columnLogger;

    /**
     * ColumnLogListener constructor.
     *
     * @param ColumnLogger $columnLogger
     */
    public function __construct(ColumnLogger $columnLogger)
    {
        $this->columnLogger = $columnLogger;
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof LoggableEntityInterface) {
                $changeSet = $uow->getEntityChangeSet($entity);

                $logs = $this->columnLogger->logColumns($entity, ColumnLogger::MOD_TYPE_INSERT, $em, $changeSet);
                foreach ($logs as $log) {
                    $em->persist($log);
                    $uow->computeChangeSet($em->getClassMetadata($log::class), $log);
                }
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof LoggableEntityInterface) {
                $changeSet = $uow->getEntityChangeSet($entity);

                $logs = $this->columnLogger->logColumns($entity, ColumnLogger::MOD_TYPE_UPDATE, $em, $changeSet);
                foreach ($logs as $log) {
                    $em->persist($log);
                    $uow->computeChangeSet($em->getClassMetadata($log::class), $log);
                }
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof LoggableEntityInterface) {
                $logs = $this->columnLogger->logColumns($entity, ColumnLogger::MOD_TYPE_DELETE, $em);
                foreach ($logs as $log) {
                    $em->persist($log);
                    $uow->computeChangeSet($em->getClassMetadata($log::class), $log);
                }
            }
        }
    }
}
