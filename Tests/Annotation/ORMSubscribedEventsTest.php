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

use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

class ORMSubscribedEventsTest extends \PHPUnit_Framework_TestCase {

    public function testConstructWithoutData()
    {
        $annotation = new ORMSubscribedEvents(array());

        $this->assertTrue(is_array($annotation->events));
        $this->assertEmpty($annotation->events);
    }

    public function testConstructWithInvalidData()
    {
        $data = array(
            'unknown'   => 'foo',
            'array'     => array('bar' => 'bar'),
        );

        $annotation = new ORMSubscribedEvents($data);

        $this->assertTrue(is_array($annotation->events));
        $this->assertEmpty($annotation->events);
    }

    public function testConstructWithValue()
    {
        $data = array("value" => "updated,created");

        $annotation = new ORMSubscribedEvents($data);

        $this->assertTrue(is_array($annotation->events));
        $this->assertNotEmpty($annotation->events);

        $this->assertEquals(explode(',', $data['value']), $annotation->events);
    }

    public function testConstructWithEvent()
    {
        $data = array("events" => "updated,created");

        $annotation = new ORMSubscribedEvents($data);

        $this->assertTrue(is_array($annotation->events));
        $this->assertNotEmpty($annotation->events);

        $this->assertEquals(explode(',', $data['events']), $annotation->events);
    }
}
 