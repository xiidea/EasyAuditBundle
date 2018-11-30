<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Subscriber;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;
use Xiidea\EasyAuditBundle\Subscriber\DoctrineDeleteEventLogger;
use Symfony\Component\Console\ConsoleEvents;

class DoctrineDeleteEventLoggerTest extends TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $responseevent;

    public function setUp()
    {
        $this->responseevent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder('Xiidea\EasyAuditBundle\Logger\Logger')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testConstructor()
    {
        $subscriber = new DoctrineDeleteEventLogger($this->logger);
        $this->assertAttributeEquals($this->logger, 'logger', $subscriber);
    }

    public function testSubscribedEvents()
    {
        $subscriber = new DoctrineDeleteEventLogger($this->logger);
        $this->assertEquals(
            [
                ConsoleEvents::TERMINATE => 'savePendingLogs',
                KernelEvents::TERMINATE  => 'savePendingLogs'
            ],
            $subscriber->getSubscribedEvents()
        );
    }

    public function testOnKernelResponse()
    {
        $this->logger
            ->expects($this->atLeastOnce())
            ->method('savePendingLogs');
        $subscriber = new DoctrineDeleteEventLogger($this->logger);
        $subscriber->savePendingLogs($this->responseevent);
    }
}
