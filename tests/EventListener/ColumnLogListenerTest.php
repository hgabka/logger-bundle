<?php

namespace Hgabka\LoggerBundle\Tests\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Hgabka\LoggerBundle\Entity\LogColumn;
use Hgabka\LoggerBundle\EventListener\ColumnLogListener;
use Hgabka\LoggerBundle\Helper\LoggableEntityInterface;
use Hgabka\LoggerBundle\Logger\ColumnLogger;
use PHPUnit\Framework\TestCase;

class ColumnLogListenerTest extends TestCase
{
    private function loggable(): LoggableEntityInterface
    {
        return new class() implements LoggableEntityInterface {
            public function getLogFields(): ?array
            {
                return [];
            }

            public function getNotLogFields(): ?array
            {
                return null;
            }
        };
    }

    public function testInsertUpdateAndDeleteAreLoggedForLoggableEntitiesOnly(): void
    {
        $inserted = $this->loggable();
        $updated = $this->loggable();
        $deleted = $this->loggable();
        $plain = new \stdClass();

        $uow = $this->createMock(UnitOfWork::class);
        $uow->method('getScheduledEntityInsertions')->willReturn([$inserted, $plain]);
        $uow->method('getScheduledEntityUpdates')->willReturn([$updated]);
        $uow->method('getScheduledEntityDeletions')->willReturn([$deleted]);
        $uow->method('getEntityChangeSet')->willReturn(['name' => ['a', 'b']]);

        $log = new LogColumn();
        $logger = $this->createStub(ColumnLogger::class);
        $calls = [];
        $logger->method('logColumns')->willReturnCallback(function ($entity, $action, $em, $changeSet = null) use (&$calls, $log) {
            $calls[] = [$entity, $action, $changeSet];

            return [$log];
        });

        $em = $this->createMock(EntityManager::class);
        $em->method('getUnitOfWork')->willReturn($uow);
        $em->method('getClassMetadata')->willReturn($this->createStub(ClassMetadata::class));
        $em->expects($this->exactly(3))->method('persist')->with($log);
        $uow->expects($this->exactly(3))->method('computeChangeSet');

        (new ColumnLogListener($logger))->onFlush(new OnFlushEventArgs($em));

        $this->assertSame(
            [
                [$inserted, ColumnLogger::MOD_TYPE_INSERT, ['name' => ['a', 'b']]],
                [$updated, ColumnLogger::MOD_TYPE_UPDATE, ['name' => ['a', 'b']]],
                [$deleted, ColumnLogger::MOD_TYPE_DELETE, null],
            ],
            $calls
        );
    }
}
