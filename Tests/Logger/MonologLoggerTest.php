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

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Xiidea\EasyAuditBundle\Logger\MonologLogger;
use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog;

class MonologLoggerTest extends TestCase
{
    /** @var MonologLogger */
    protected $logger;

    /** @var MockObject| LoggerInterface */
    protected $symfonyLogger;

    public function setUp(): void    {
        $this->symfonyLogger = $this->createMock(LoggerInterface::class);

        $this->logger = new MonologLogger($this->symfonyLogger);
    }

    public function testIsAnInstanceOfLoggerInterface()
    {
        $this->assertInstanceOf('Xiidea\EasyAuditBundle\Logger\LoggerInterface', $this->logger);
    }

    public function testLogCallsLogToLogServiceWithLogArray()
    {
        $log = new AuditLog();

        $this->symfonyLogger
            ->expects($this->at(0))
            ->method('log')
            ->with(
                $this->equalTo('info'),
                $log->getDescription(),
                array(
                    'typeId' => null,
                    'type' => null,
                    'eventTime' => null,
                    'user' => null,
                    'ip' => null,
                    'impersonatingUser' => null,
                )
            );
        $this->logger->log($log);
    }

    public function testShouldNotCallLogWithEmptyAuditEntry()
    {
        $this->symfonyLogger
            ->expects($this->never())
            ->method('log');

        $this->logger->log(null);
    }
}
