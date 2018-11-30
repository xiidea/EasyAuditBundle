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

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;
use Xiidea\EasyAuditBundle\Logger\Logger;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ODM\AuditLog;
use Xiidea\EasyAuditBundle\Logger\LoggerInterface;
use Xiidea\EasyAuditBundle\Document\BaseAuditLog;

class LoggerTest extends TestCase
{
    /** @var Logger */
    protected $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $documentManager;

    public function setUp()
    {
        $registry = $this
            ->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->documentManager = $this
            ->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->documentManager));

        $this->logger = new Logger($registry);
    }

    public function testIsAnInstanceOfLoggerInterface()
    {
        $this->assertInstanceOf(LoggerInterface::class, $this->logger);
    }

    public function testLogCallsPersistWithDoctrineForAuditLogObject()
    {
        $this->documentManager
            ->expects($this->at(0))
            ->method('persist')
            ->with($this->isInstanceOf(BaseAuditLog::class));
        $this->documentManager
            ->expects($this->at(1))
            ->method('flush')
            ->with($this->isInstanceOf(BaseAuditLog::class));

        $this->logger->log(new AuditLog());
    }

    public function testLogCallsDeferred()
    {
        $this->documentManager
            ->expects($this->never())
            ->method('persist');
        $this->documentManager
            ->expects($this->never())
            ->method('flush');

        $event = new AuditLog();
        $event->setTypeId(DoctrineEvents::DOCUMENT_DELETED);
        $this->logger->log($event);
        $this->assertAttributeEquals([$event], 'documentDeleteLogs', $this->logger);
    }

    public function testSavePendingLogsForDelete()
    {
        $this->documentManager
            ->expects($this->at(0))
            ->method('persist')
            ->with($this->isInstanceOf(BaseAuditLog::class));
        $this->documentManager
            ->expects($this->at(1))
            ->method('flush')
            ->with($this->isInstanceOf(BaseAuditLog::class));

        $event = new AuditLog();
        $event->setTypeId(DoctrineEvents::DOCUMENT_DELETED);
        $this->logger->log($event);
        $this->assertAttributeEquals([$event], 'documentDeleteLogs', $this->logger);
        $this->logger->savePendingLogs();
        $this->assertAttributeEquals([], 'documentDeleteLogs', $this->logger);
    }

    public function testLogDoesNotCallToPersist()
    {
        $this->documentManager
            ->expects($this->never())
            ->method('persist');
        $this->documentManager
            ->expects($this->never())
            ->method('flush');

        $this->logger->log(null);
    }
}
 