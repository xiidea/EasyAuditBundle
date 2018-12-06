<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Listener;

use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Listener\LogEventsListener;
use Xiidea\EasyAuditBundle\Logger\LoggerFactory;
use Xiidea\EasyAuditBundle\Resolver\EventResolverFactory;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\Basic;

class LogEventsListenerTest extends TestCase
{
    public function testCheckPropertySetViaConstructor()
    {
        $loggerFactory = new LoggerFactory();
        $resolverFactory = new EventResolverFactory();
        $logEventsListener = new LogEventsListener($loggerFactory, $resolverFactory);
        $this->assertAttributeEquals($loggerFactory, 'loggerFactory', $logEventsListener);
        $this->assertAttributeEquals($resolverFactory, 'resolverFactory', $logEventsListener);
    }

    public function testResolveEventHandler()
    {
        $event = new Basic();

        $loggerFactory = $this->createMock('Xiidea\EasyAuditBundle\Logger\LoggerFactory');
        $resolverFactory = $this->createMock('Xiidea\EasyAuditBundle\Resolver\EventResolverFactory');

        $logEventsListener = new LogEventsListener($loggerFactory, $resolverFactory);
        $eventName = 'basic_event';
        $eventInfo = array('type' => $eventName);

        $resolverFactory->expects($this->once())
            ->method('getEventLog')
            ->with($this->equalTo($event))
            ->willReturn($eventInfo);

        $loggerFactory->expects($this->once())
            ->method('executeLoggers')
            ->with($this->equalTo($eventInfo));

        $logEventsListener->resolveEventHandler($event, $eventName);
    }
}
