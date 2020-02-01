<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLogEntity;

class BaseAuditLogTest extends TestCase
{
    public function testSettingInvalidLevelWillThrowException()
    {
        $log = new AuditLog();
        $this->expectException(InvalidArgumentException::class);
        $log->setLevel('invalid_level');
    }

    public function testSettingLevelCase()
    {
        $log = new AuditLog();
        $log->setLevel('info');
        $this->assertEquals('info', $log->getLevel());
        $log->setLevel('INFO');
        $this->assertEquals('INFO', $log->getLevel());
    }

    public function testDeprecatedAuditLog()
    {
        $log = new AuditLogEntity();
        $log->setLevel('info');
        $this->assertEquals('info', $log->getLevel());
        $log->setLevel('INFO');
        $this->assertEquals('INFO', $log->getLevel());
    }
}
