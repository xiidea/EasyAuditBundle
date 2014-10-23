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

use Xiidea\EasyAuditBundle\DependencyInjection\Compiler\SubscriberPass;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\EasySubscriberOne;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\EasySubscriberTwo;

class SubscriberPassTest extends \PHPUnit_Framework_TestCase {

    public function testProcessWithoutEventListenerDefinition()
    {
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo("xiidea.easy_audit.event_listener"))
            ->will($this->returnValue(false));
        $containerBuilder->expects($this->never())
            ->method('findTaggedServiceIds');

        $this->processSubscriberPass($containerBuilder);
    }

    public function testDisableDoctrineEvents()
    {
        $containerBuilder = $this->getContainerBuilderMock();

        $this->containerExpectsForDisabledDoctrineEvents($containerBuilder);

        $this->processSubscriberPass($containerBuilder);
    }

    public function testEnableDoctrineEvents()
    {
        $containerBuilder = $this->getContainerBuilderMock();
        $this->containerExpectsForEnabledDoctrineEvents($containerBuilder);

        $this->processSubscriberPass($containerBuilder);
    }

    public function testSubscribedEventsWithDisableDoctrineEvents()
    {
        $containerBuilder = $this->getContainerBuilderMock();

        $subscribers = array(
            'easy.subscriber1' =>
                array(
                    array(
                        'resolver' => 'aresolver'
                    )
                ),
            'easy.subscriber2' =>
                array(
                    array()
                )
        );

        $this->containerExpectsForDisabledDoctrineEvents($containerBuilder, $subscribers);

        $definitionMock = $this->getDefinitionMock();

        $listenableEventsList = array (
                array (
                    'event' => 'event1',
                    'method' => 'resolveEventHandler',
                ),
                array (
                    'event' => 'custom_event1',
                    'method' => 'resolveEventHandler',
                ),
                array (
                    'event' => 'custom_event2',
                    'method' => 'resolveEventHandler',
                ),
                array (
                    'event' => 'common_event',
                    'method' => 'resolveEventHandler',
                ),
                array (
                    'event' => 'common_event1',
                    'method' => 'resolveEventHandler',
                ),
                array (
                    'event' => 'common_event2',
                    'method' => 'resolveEventHandler',
                )
        );

        $definitionMock
            ->expects($this->at(0))
            ->method('setTags')
            ->with($this->equalTo(array('kernel.event_listener' => $listenableEventsList)));;


        $containerBuilder
            ->expects($this->at(4))
            ->method('get')
            ->with($this->equalTo('easy.subscriber1'))
            ->will($this->returnValue(new EasySubscriberOne()));
        $containerBuilder
            ->expects($this->at(5))
            ->method('get')
            ->with($this->equalTo('easy.subscriber2'))
            ->will($this->returnValue(new EasySubscriberTwo()));

        $containerBuilder
            ->expects($this->at(6))
            ->method('getDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_listener'))
            ->will($this->returnValue($definitionMock));


        $containerBuilder
            ->expects($this->at(7))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'))
            ->will($this->returnValue(array()));
        $containerBuilder
            ->expects($this->at(8))
            ->method('setParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'), array (
                'event1' => 'custom_resolver1',
                'custom_event1' => 'custom_resolver2',
                'custom_event2' => 'custom_resolver2',
                'common_event' => 'aresolver',
            ));

        $this->processSubscriberPass($containerBuilder);
    }

    public function testSubscribedEventsWithEnabledDoctrineEvents()
    {
        $containerBuilder = $this->getContainerBuilderMock();

        $subscribers = array(
            'easy.subscriber1' =>
                array(
                    array(
                        'resolver' => 'aresolver'
                    )
                ),
            'easy.subscriber2' =>
                array(
                    array()
                )
        );

        $listenableEventsList =array (
            array (
                'event' => 'easy_audit.doctrine.entity.updated',
                'method' => 'resolveEventHandler',
            ),
            array (
                'event' => 'easy_audit.doctrine.entity.created',
                'method' => 'resolveEventHandler',
            ),
            array (
                'event' => 'easy_audit.doctrine.entity.deleted',
                'method' => 'resolveEventHandler',
            ),
            array (
                'event' => 'event1',
                'method' => 'resolveEventHandler',
            ),
            array (
                'event' => 'custom_event1',
                'method' => 'resolveEventHandler',
            ),
            array (
                'event' => 'custom_event2',
                'method' => 'resolveEventHandler',
            ),
            array (
                'event' => 'common_event',
                'method' => 'resolveEventHandler',
            ),
            array (
                'event' => 'common_event1',
                'method' => 'resolveEventHandler',
            ),
            array (
                'event' => 'common_event2',
                'method' => 'resolveEventHandler',
            )
        );

        $definitionMock = $this->getDefinitionMock();

        $definitionMock
            ->expects($this->at(0))
            ->method('setTags')
            ->with($this->equalTo(array('kernel.event_listener' => $listenableEventsList)));

        $containerBuilder
            ->expects($this->at(1))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.events'))
            ->will($this->returnValue(array()));
        $containerBuilder
            ->expects($this->at(2))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.doctrine_entities'))
            ->will($this->returnValue(true));
        $containerBuilder
            ->expects($this->at(3))
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('easy_audit.event_subscriber'))
            ->will($this->returnValue($subscribers));

        $containerBuilder
            ->expects($this->at(4))
            ->method('get')
            ->with($this->equalTo('easy.subscriber1'))
            ->will($this->returnValue(new EasySubscriberOne()));
        $containerBuilder
            ->expects($this->at(5))
            ->method('get')
            ->with($this->equalTo('easy.subscriber2'))
            ->will($this->returnValue(new EasySubscriberTwo()));

        $containerBuilder
            ->expects($this->at(6))
            ->method('getDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_listener'))
            ->will($this->returnValue($definitionMock));

        $containerBuilder
            ->expects($this->at(7))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'))
            ->will($this->returnValue(array()));

        $containerBuilder
            ->expects($this->at(8))
            ->method('setParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'), array (
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
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo("xiidea.easy_audit.event_listener"))
            ->will($this->returnValue(true));

        return $containerBuilder;
    }

    /**
     * @param $containerBuilder
     * @param array $subscribedEvents
     */
    protected function containerExpectsForEnabledDoctrineEvents($containerBuilder, $subscribedEvents = array())
    {
        $definitionMock = $this->getDefinitionMock();

        $listenableEventsList = array(
            array(
                'event' => 'easy_audit.doctrine.entity.updated',
                'method' => 'resolveEventHandler',
            ),
            array(
                'event' => 'easy_audit.doctrine.entity.created',
                'method' => 'resolveEventHandler',
            ),
            array(
                'event' => 'easy_audit.doctrine.entity.deleted',
                'method' => 'resolveEventHandler',
            )
        );

        $definitionMock
            ->expects($this->at(0))
            ->method('setTags')
            ->with($this->equalTo(array('kernel.event_listener' => $listenableEventsList)));

        $containerBuilder
            ->expects($this->at(1))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.events'))
            ->will($this->returnValue(array()));
        $containerBuilder
            ->expects($this->at(2))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.doctrine_entities'))
            ->will($this->returnValue(true));
        $containerBuilder
            ->expects($this->at(3))
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('easy_audit.event_subscriber'))
            ->will($this->returnValue($subscribedEvents));
        $containerBuilder
            ->expects($this->at(4))
            ->method('getDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_listener'))
            ->will($this->returnValue($definitionMock));
        $containerBuilder
            ->expects($this->at(5))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'))
            ->will($this->returnValue(array()));
    }

    /**
     * @param $containerBuilder
     * @param array $subscribedEvents
     */
    protected function containerExpectsForDisabledDoctrineEvents($containerBuilder, $subscribedEvents = array())
    {
        $containerBuilder
            ->expects($this->at(1))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.events'))
            ->will($this->returnValue(array()));
        $containerBuilder
            ->expects($this->at(2))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.doctrine_entities'))
            ->will($this->returnValue(false));
        $containerBuilder
            ->expects($this->at(3))
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
