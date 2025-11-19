<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Attribute;

use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Attribute\SubscribeDoctrineEvents;

class SubscribeDoctrineEventsTest extends TestCase
{
    public function testConstructWithoutData()
    {
        $annotation = new SubscribeDoctrineEvents([]);

        $this->assertTrue(is_array($annotation->events));
        $this->assertEmpty($annotation->events);
        $annotation = new SubscribeDoctrineEvents("");
        $this->assertEmpty($annotation->events);
    }


    public function testConstructWithStringValue()
    {
        $data = 'created,updated';

        $annotation = new SubscribeDoctrineEvents($data);

        $this->assertTrue(is_array($annotation->events));
        $this->assertNotEmpty($annotation->events);

        $this->assertEquals(explode(',', $data), $annotation->events);
    }

    public function testConstructWithArrayValue()
    {
        $data = ['created', 'updated'];

        $annotation = new SubscribeDoctrineEvents($data);

        $this->assertTrue(is_array($annotation->events));
        $this->assertNotEmpty($annotation->events);

        $this->assertEquals($data, $annotation->events);
    }
}
