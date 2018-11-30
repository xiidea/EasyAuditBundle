<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Annotation;

use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Annotation\ODMSubscribedEvents;

class ODMSubscribedEventsTest extends TestCase
{
    public function testConstructWithoutData()
    {
        $annotation = new ODMSubscribedEvents([]);

        $this->assertInternalType('array', $annotation->events);
        $this->assertEmpty($annotation->events);
    }

    public function testConstructWithInvalidData()
    {
        $data = [
            'unknown' => 'foo',
            'array'   => ['bar' => 'bar'],
        ];

        $annotation = new ODMSubscribedEvents($data);

        $this->assertInternalType('array', $annotation->events);
        $this->assertEmpty($annotation->events);
    }

    public function testConstructWithValue()
    {
        $data = ['value' => 'updated,created'];

        $annotation = new ODMSubscribedEvents($data);

        $this->assertInternalType('array', $annotation->events);
        $this->assertNotEmpty($annotation->events);

        $this->assertEquals(explode(',', $data['value']), $annotation->events);
    }

    public function testConstructWithEvent()
    {
        $data = ['events' => 'updated,created'];

        $annotation = new ODMSubscribedEvents($data);

        $this->assertInternalType('array', $annotation->events);
        $this->assertNotEmpty($annotation->events);

        $this->assertEquals(explode(',', $data['events']), $annotation->events);
    }
}
 