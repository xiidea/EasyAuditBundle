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


use Xiidea\EasyAuditBundle\Logger\LoggerFactory;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog;

class LoggerFactoryTest extends \PHPUnit_Framework_TestCase {

    /** @var  \PHPUnit_Framework_MockObject_MockObject  */
    private $container;

    /** @var  \PHPUnit_Framework_MockObject_MockObject  */
    private $kernel;

    /** @var  LoggerFactory */
    private $loggerFactory;

    public function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $this->loggerFactory =  new LoggerFactory();
        $this->loggerFactory->setContainer($this->container);
    }


    /**
     * @expectedException \Xiidea\EasyAuditBundle\Exception\InvalidServiceException
     */
    public function testThrowsExceptionOnAddInvalidLoggerWithDebugModeOn()
    {
        $this->initiateContainerWithDebugMode(true);
        $this->loggerFactory->addLogger("invalid", new AuditLog());
    }

    public function testDoNotThrowsExceptionOnAddInvalidLoggerWithDebugModeDisable()
    {
        $this->initiateContainerWithDebugMode(false);
        $this->loggerFactory->addLogger("invalid", new AuditLog());
    }

    public function testLoggerFactoryThrowsNoExceptionOnAddValidLogger()
    {
        $loger1 = $this->getMock('Xiidea\EasyAuditBundle\Logger\LoggerInterface');

        $loggerFactory = new LoggerFactory();
        $loggerFactory->addLogger("valid", $loger1);
    }

    public function testExecuteAllLoggers() {
        $loger1 = $this->getMock('Xiidea\EasyAuditBundle\Logger\LoggerInterface');
        $loger2 = $this->getMock('Xiidea\EasyAuditBundle\Logger\LoggerInterface');

        $eventInfo = new AuditLog();

        $loger1->expects($this->once())
            ->method('log')
            ->with($this->equalTo($eventInfo));

        $loger2->expects($this->once())
            ->method('log')
            ->with($this->equalTo($eventInfo));

        $loggerFactory = new LoggerFactory();
        $loggerFactory->addLogger("logger1",$loger1);
        $loggerFactory->addLogger("logger2",$loger2);


        $loggerFactory->executeLoggers($eventInfo);
    }

    public function testExecuteOnlyValidLoggers() {
        $this->initiateContainerWithDebugMode(false);

        $validLogger = $this->getMock('Xiidea\EasyAuditBundle\Logger\LoggerInterface');
        $inValidLogger = $this->getMock('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog');

        $eventInfo = new AuditLog();

        $validLogger->expects($this->once())
            ->method('log')
            ->with($this->equalTo($eventInfo));

        $inValidLogger->expects($this->never())
            ->method('log');

        $this->loggerFactory->addLogger("logger1",$validLogger);
        $this->loggerFactory->addLogger("logger2",$inValidLogger);

        $this->loggerFactory->executeLoggers($eventInfo);
    }

    public function testDoesNotExecuteLogForEmptyEventInfo() {
        $loger1 = $this->getMock('Xiidea\EasyAuditBundle\Logger\LoggerInterface');

        $loger1->expects($this->never())
            ->method('log');

        $loggerFactory = new LoggerFactory();
        $loggerFactory->addLogger("logger1",$loger1);

        $loggerFactory->executeLoggers(null);
    }

    /**
     * @param bool $on
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function initiateContainerWithDebugMode($on = true)
    {
        $this->kernel->expects($this->once())
            ->method('isDebug')
            ->will($this->returnValue($on));

        $this->container->expects($this->once())
            ->method('get')
            ->with($this->equalTo('kernel'))
            ->will($this->returnValue($this->kernel));
    }
}
 