<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Entity;


use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog;

class BaseAuditLogTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \Psr\Log\InvalidArgumentException
     */
    public function testSettingInvalidLevelWillThrowException()
    {
        $log = new AuditLog();
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
}
