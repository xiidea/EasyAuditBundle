<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\DependencyInjection\Compiler\SubscriberPass;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\EasySubscriberOne;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\EasySubscriberTwo;

class SubscriberPassTest extends TestCase
{
    public function testProcessWithoutEventListenerDefinition()
    {
        $containerBuilder = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_listener'))
            ->will($this->returnValue(false));
        $containerBuilder->expects($this->never())
            ->method('findTaggedServiceIds');

        $this->processSubscriberPass($containerBuilder);
    }

    public function testDisableDoctrineEvents()
    {
        $containerBuilder = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->any())
            ->method('hasDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_listener'))
            ->will($this->returnValue(true));

        $containerBuilder->expects($this->any())
            ->method('getParameter')
            ->willReturnMap([
                ['xiidea.easy_audit.events', []],
                ['xiidea.easy_audit.doctrine_objects', false],
            ]);

        $containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('easy_audit.event_subscriber'))
            ->will($this->returnValue([]));

        $this->processSubscriberPass($containerBuilder);
    }

    public function testEnableDoctrineEvents()
    {
        $containerBuilder = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->any())
            ->method('hasDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_listener'))
            ->will($this->returnValue(true));

        $containerBuilder->expects($this->any())
            ->method('getParameter')
            ->willReturnMap([
                ['xiidea.easy_audit.events', []],
                ['xiidea.easy_audit.doctrine_objects', true],
                ['xiidea.easy_audit.custom_resolvers', []],
            ]);

        $definitionMock = $this->getDefinitionMock();

        $containerBuilder
            ->method('getDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_listener'))
            ->will($this->returnValue($definitionMock));

        $definitionMock
            ->expects($this->once())
            ->method('setTags');

        $this->processSubscriberPass($containerBuilder);
    }

    public function testSubscribedEventsWithDisableDoctrineEvents()
    {
        $subscribers = array(
            'easy.subscriber1' => array(
                    array(
                        'resolver' => 'aresolver',
                    ),
                ),
            'easy.subscriber2' => array(
                    array(),
                ),
        );

        $containerBuilder = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->any())
            ->method('hasDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_listener'))
            ->will($this->returnValue(true));

        $containerBuilder->expects($this->any())
            ->method('getParameter')
            ->willReturnMap([
                ['xiidea.easy_audit.events', []],
                ['xiidea.easy_audit.doctrine_objects', false],
                ['xiidea.easy_audit.custom_resolvers', []],
            ]);

        $containerBuilder
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('easy_audit.event_subscriber'))
            ->will($this->returnValue($subscribers));

        $definitionMock = $this->getDefinitionMock();

        $containerBuilder
            ->method('getDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_listener'))
            ->will($this->returnValue($definitionMock));

        $containerBuilder
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                array($this->equalTo('easy.subscriber1')),
                array($this->equalTo('easy.subscriber2'))
            )
            ->willReturnOnConsecutiveCalls(
                new EasySubscriberOne(),
                new EasySubscriberTwo()
            );

        $listenableEventsList = array(
                array(
                    'event' => 'event1',
                    'method' => 'resolveEventHandler',
                ),
                array(
                    'event' => 'custom_event1',
                    'method' => 'resolveEventHandler',
                ),
                array(
                    'event' => 'custom_event2',
                    'method' => 'resolveEventHandler',
                ),
                array(
                    'event' => 'common_event',
                    'method' => 'resolveEventHandler',
                ),
                array(
                    'event' => 'common_event1',
                    'method' => 'resolveEventHandler',
                ),
                array(
                    'event' => 'common_event2',
                    'method' => 'resolveEventHandler',
                ),
        );

        $definitionMock
            ->expects($this->once())
            ->method('setTags')
            ->with($this->equalTo(array('kernel.event_listener' => $listenableEventsList)));

        $containerBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'), array(
                'event1' => 'custom_resolver1',
                'custom_event1' => 'custom_resolver2',
                'custom_event2' => 'custom_resolver2',
                'common_event' => 'aresolver',
            ));

        $this->processSubscriberPass($containerBuilder);
    }

    public function testSubscribedEventsWithEnabledDoctrineEvents()
    {
        $subscribers = array(
            'easy.subscriber1' => array(
                    array(
                        'resolver' => 'aresolver',
                    ),
                ),
            'easy.subscriber2' => array(
                    array(),
                ),
        );

        $containerBuilder = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->any())
            ->method('hasDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_listener'))
            ->will($this->returnValue(true));

        $containerBuilder->expects($this->any())
            ->method('getParameter')
            ->willReturnMap([
                ['xiidea.easy_audit.events', []],
                ['xiidea.easy_audit.doctrine_objects', true],
                ['xiidea.easy_audit.custom_resolvers', []],
            ]);

        $containerBuilder
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('easy_audit.event_subscriber'))
            ->will($this->returnValue($subscribers));

        $definitionMock = $this->getDefinitionMock();

        $containerBuilder
            ->method('getDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_listener'))
            ->will($this->returnValue($definitionMock));

        $containerBuilder
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                array($this->equalTo('easy.subscriber1')),
                array($this->equalTo('easy.subscriber2'))
            )
            ->willReturnOnConsecutiveCalls(
                new EasySubscriberOne(),
                new EasySubscriberTwo()
            );

        $listenableEventsList = array(
            array(
                'event' => 'easy_audit.doctrine.object.updated',
                'method' => 'resolveEventHandler',
            ),
            array(
                'event' => 'easy_audit.doctrine.object.created',
                'method' => 'resolveEventHandler',
            ),
            array(
                'event' => 'easy_audit.doctrine.object.deleted',
                'method' => 'resolveEventHandler',
            ),
            array(
                'event' => 'event1',
                'method' => 'resolveEventHandler',
            ),
            array(
                'event' => 'custom_event1',
                'method' => 'resolveEventHandler',
            ),
            array(
                'event' => 'custom_event2',
                'method' => 'resolveEventHandler',
            ),
            array(
                'event' => 'common_event',
                'method' => 'resolveEventHandler',
            ),
            array(
                'event' => 'common_event1',
                'method' => 'resolveEventHandler',
            ),
            array(
                'event' => 'common_event2',
                'method' => 'resolveEventHandler',
            ),
        );

        $definitionMock
            ->expects($this->once())
            ->method('setTags')
            ->with($this->equalTo(array('kernel.event_listener' => $listenableEventsList)));

        $containerBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'), array(
                'event1' => 'custom_resolver1',
                'custom_event1' => 'custom_resolver2',
                'custom_event2' => 'custom_resolver2',
                'common_event' => 'aresolver',
            ));

        $this->processSubscriberPass($containerBuilder);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDefinitionMock()
    {
        $definitionMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        return $definitionMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContainerBuilderMock()
    {
        $containerBuilder = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->any())
            ->method('hasDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_listener'))
            ->will($this->returnValue(true));

        $containerBuilder->expects($this->any())
            ->method('getParameter')
            ->willReturnMap([
                ['xiidea.easy_audit.events', []],
                ['xiidea.easy_audit.doctrine_objects', false],
                ['xiidea.easy_audit.custom_resolvers', []],
            ]);

        return $containerBuilder;
    }

    /**
     * @param $containerBuilder \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\DependencyInjection\ContainerBuilder
     * @param array $subscribedEvents
     */
    protected function containerExpectsForEnabledDoctrineEvents($containerBuilder, $subscribedEvents = array())
    {
        $definitionMock = $this->getDefinitionMock();

        $listenableEventsList = array(
            array(
                'event' => 'easy_audit.doctrine.object.updated',
                'method' => 'resolveEventHandler',
            ),
            array(
                'event' => 'easy_audit.doctrine.object.created',
                'method' => 'resolveEventHandler',
            ),
            array(
                'event' => 'easy_audit.doctrine.object.deleted',
                'method' => 'resolveEventHandler',
            ),
        );

        $definitionMock
            ->expects($this->once())
            ->method('setTags')
            ->with($this->equalTo(array('kernel.event_listener' => $listenableEventsList)));

        $containerBuilder->expects($this->any())
            ->method('getParameter')
            ->willReturnMap([
                ['xiidea.easy_audit.events', []],
                ['xiidea.easy_audit.doctrine_objects', true],
                ['xiidea.easy_audit.custom_resolvers', []],
            ]);

        $containerBuilder
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('easy_audit.event_subscriber'))
            ->will($this->returnValue($subscribedEvents));

        $containerBuilder
            ->method('getDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_listener'))
            ->will($this->returnValue($definitionMock));
    }

    /**
     * @param $containerBuilder \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\DependencyInjection\ContainerBuilder
     * @param array $subscribedEvents
     */
    protected function containerExpectsForDisabledDoctrineEvents($containerBuilder, $subscribedEvents = array())
    {
        $containerBuilder
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('easy_audit.event_subscriber'))
            ->will($this->returnValue($subscribedEvents));
    }

    /**
     * @param $containerBuilder
     */
    protected function processSubscriberPass($containerBuilder)
    {
        $subscriberPass = new SubscriberPass();
        $subscriberPass->process($containerBuilder);
    }
}
