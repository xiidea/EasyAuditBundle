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

use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Logger\Logger;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog;

class LoggerTest extends TestCase {

    /** @var Logger */
    protected $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $entityManager;

    public function setUp()
    {
        $registry = $this
            ->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));

        $this->logger =  new Logger($registry);
    }

    public function testIsAnInstanceOfLoggerInterface()
    {
        $this->assertInstanceOf('Xiidea\EasyAuditBundle\Logger\LoggerInterface', $this->logger);
    }

    public function testLogCallsPersistWithDoctrineForAuditLogObject()
    {

        $this->entityManager
            ->expects($this->at(0))
            ->method("persist")
            ->with($this->isInstanceOf('Xiidea\EasyAuditBundle\Entity\BaseAuditLog'));
        $this->entityManager
            ->expects($this->at(1))
            ->method("flush")
            ->with($this->isInstanceOf('Xiidea\EasyAuditBundle\Entity\BaseAuditLog'));


        $this->logger->log(new AuditLog());

    }

    public function testLogDoesNotCallToPersist()
    {
        $this->entityManager
            ->expects($this->never())
            ->method("persist");
        $this->entityManager
            ->expects($this->never())
            ->method("flush");

        $this->logger->log(null);
    }
}
 