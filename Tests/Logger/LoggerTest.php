<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Logger;

use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;
use Xiidea\EasyAuditBundle\Logger\Logger;
use Xiidea\EasyAuditBundle\Model\BaseAuditLog;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog;

class LoggerTest extends TestCase
{
    /** @var Logger */
    protected $logger;

    /** @var MockObject */
    protected $entityManager;

    public function setUp(): void    {
        $registry = $this
            ->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));

        $this->logger = new Logger($registry);
    }

    public function testIsAnInstanceOfLoggerInterface()
    {
        $this->assertInstanceOf('Xiidea\EasyAuditBundle\Logger\LoggerInterface', $this->logger);
    }

    public function testLogCallsPersistWithDoctrineForAuditLogObject()
    {
        $this->entityManager
            ->expects($this->at(0))
            ->method('persist')
            ->with($this->isInstanceOf(BaseAuditLog::class));
        $this->entityManager
            ->expects($this->at(1))
            ->method('flush')
            ->with($this->isInstanceOf(BaseAuditLog::class));

        $this->logger->log(new AuditLog());
    }

    public function testLogCallsDeferred()
    {
        $this->entityManager
            ->expects($this->never())
            ->method('persist');
        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        $event = new AuditLog();
        $event->setTypeId(DoctrineEvents::ENTITY_DELETED);
        $this->logger->log($event);
    }

    public function testSavePendingLogsForDelete()
    {
        $this->entityManager
            ->expects($this->at(0))
            ->method('persist')
            ->with($this->isInstanceOf(BaseAuditLog::class));
        $this->entityManager
            ->expects($this->at(1))
            ->method('flush')
            ->with($this->isInstanceOf(BaseAuditLog::class));

        $event = new AuditLog();
        $event->setTypeId(DoctrineEvents::ENTITY_DELETED);
        $this->logger->log($event);
        $this->logger->savePendingLogs();
    }

    public function testLogDoesNotCallToPersist()
    {
        $this->entityManager
            ->expects($this->never())
            ->method('persist');
        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        $this->logger->log(null);
    }
}
