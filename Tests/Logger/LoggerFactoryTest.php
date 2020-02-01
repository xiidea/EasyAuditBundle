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
use Xiidea\EasyAuditBundle\Exception\InvalidServiceException;
use Xiidea\EasyAuditBundle\Logger\LoggerFactory;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\InvalidLogger;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog;

class LoggerFactoryTest extends TestCase
{
    /** @var LoggerFactory */
    private $loggerFactory;

    private $channel = array(
        'logger1' => array(
            'type' => 'inclusive',
            'elements' => array('info', 'debug'),
        ),
        'logger2' => array(
            'type' => 'exclusive',
            'elements' => array('info'),
        ),
    );

    public function setUp(): void    {
        $this->loggerFactory = new LoggerFactory();
    }

    public function testThrowsExceptionOnAddInvalidLoggerWithDebugModeOn()
    {
        $this->initiateContainerWithDebugMode(true);
        $this->expectException(InvalidServiceException::class);
        $this->loggerFactory->addLogger('invalid', new InvalidLogger());
    }

    public function testExecuteAllLoggers()
    {
        $logger1 = $this->createMock('Xiidea\EasyAuditBundle\Logger\LoggerInterface');
        $logger2 = $this->createMock('Xiidea\EasyAuditBundle\Logger\LoggerInterface');

        $eventInfo = new AuditLog();

        $logger1->expects($this->once())
            ->method('log')
            ->with($this->equalTo($eventInfo));

        $logger2->expects($this->once())
            ->method('log')
            ->with($this->equalTo($eventInfo));

        $loggerFactory = new LoggerFactory();
        $loggerFactory->addLogger('logger1', $logger1);
        $loggerFactory->addLogger('logger2', $logger2);

        $loggerFactory->executeLoggers($eventInfo);
    }

    public function testExecuteOnlyValidLoggers()
    {
        $this->initiateContainerWithDebugMode(false);

        $validLogger = $this->createMock('Xiidea\EasyAuditBundle\Logger\LoggerInterface');
        $inValidLogger = $this->createMock(InvalidLogger::class);

        $eventInfo = new AuditLog();

        $validLogger->expects($this->once())
            ->method('log')
            ->with($this->equalTo($eventInfo));

        $inValidLogger->expects($this->never())
            ->method('log');

        $this->loggerFactory->addLogger('logger1', $validLogger);
        $this->loggerFactory->addLogger('logger2', $inValidLogger);

        $this->loggerFactory->executeLoggers($eventInfo);
    }

    public function testDoesNotExecuteLogForEmptyEventInfo()
    {
        $logger1 = $this->createMock('Xiidea\EasyAuditBundle\Logger\LoggerInterface');

        $logger1->expects($this->never())
            ->method('log');

        $loggerFactory = new LoggerFactory();
        $loggerFactory->addLogger('logger1', $logger1);

        $loggerFactory->executeLoggers(null);
    }

    public function testOnlyExecuteLogForRegisteredChannel()
    {
        $logger1 = $this->createMock('Xiidea\EasyAuditBundle\Logger\LoggerInterface');
        $logger2 = $this->createMock('Xiidea\EasyAuditBundle\Logger\LoggerInterface');

        $eventInfo = new AuditLog();

        $logger1->expects($this->once())
            ->method('log')
            ->with($this->equalTo($eventInfo));

        $logger2->expects($this->never())
            ->method('log');

        $loggerFactory = new LoggerFactory($this->channel);

        $loggerFactory->addLogger('logger1', $logger1);
        $loggerFactory->addLogger('logger2', $logger2);

        $loggerFactory->executeLoggers($eventInfo);
    }

    public function testExecuteLogIfChannelIsNotDefined()
    {
        $logger1 = $this->createMock('Xiidea\EasyAuditBundle\Logger\LoggerInterface');
        $logger2 = $this->createMock('Xiidea\EasyAuditBundle\Logger\LoggerInterface');

        $eventInfo = new AuditLog();

        $logger1->expects($this->once())
            ->method('log')
            ->with($this->equalTo($eventInfo));

        $logger2->expects($this->once())
            ->method('log')
            ->with($this->equalTo($eventInfo));

        $loggerFactory = new LoggerFactory();

        $loggerFactory->addLogger('logger1', $logger1);
        $loggerFactory->addLogger('logger2', $logger2);

        $loggerFactory->executeLoggers($eventInfo);
    }

    public function testInvalidChannelType()
    {
        $logger1 = $this->createMock('Xiidea\EasyAuditBundle\Logger\LoggerInterface');

        $eventInfo = new AuditLog();

        $logger1->expects($this->never())
            ->method('log');

        $loggerFactory = new LoggerFactory(array(
                'logger1' => array(
                    'type' => 'invalid',
                    'elements' => array('info'),
                ),
            ));

        $loggerFactory->addLogger('logger1', $logger1);

        $loggerFactory->executeLoggers($eventInfo);
    }

    /**
     * @param bool $on
     */
    protected function initiateContainerWithDebugMode($on = true)
    {
        $this->loggerFactory->setDebug($on);
    }
}
