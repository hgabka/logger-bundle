<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Hgabka\LoggerBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Hgabka\LoggerBundle\Helper\LoggableEntityInterface;
use Hgabka\LoggerBundle\Logger\ColumnLogger;

class ColumnLogListener
{
    /** @var ColumnLogger */
    protected $columnLogger;

    /**
     * ColumnLogListener constructor.
     *
     * @param ColumnLogger $columnLogger
     */
    public function __construct(ColumnLogger $columnLogger)
    {
        $this->columnLogger = $columnLogger;
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof LoggableEntityInterface) {
                $changeSet = $uow->getEntityChangeSet($entity);

                $logs = $this->columnLogger->logColumns($entity, ColumnLogger::MOD_TYPE_INSERT, $em->getClassMetadata(get_class($entity)), $changeSet);
                foreach ($logs as $log) {
                    $em->persist($log);
                    $uow->computeChangeSet($em->getClassMetadata(get_class($log)), $log);
                }
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof LoggableEntityInterface) {
                $changeSet = $uow->getEntityChangeSet($entity);

                $logs = $this->columnLogger->logColumns($entity, ColumnLogger::MOD_TYPE_UPDATE, $em->getClassMetadata(get_class($entity)), $changeSet);
                foreach ($logs as $log) {
                    $em->persist($log);
                    $uow->computeChangeSet($em->getClassMetadata(get_class($log)), $log);
                }
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof LoggableEntityInterface) {
                $logs = $this->columnLogger->logColumns($entity, ColumnLogger::MOD_TYPE_DELETE, $em->getClassMetadata(get_class($entity)), $changeSet);
                foreach ($logs as $log) {
                    $em->persist($log);
                    $uow->computeChangeSet($em->getClassMetadata(get_class($log)), $log);
                }
            }
        }
    }
}
