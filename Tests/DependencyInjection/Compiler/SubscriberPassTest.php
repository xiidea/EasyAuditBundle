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

use Symfony\Component\DependencyInjection\Reference;
use Xiidea\EasyAuditBundle\DependencyInjection\Compiler\SubscriberPass;

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

        $subscriberPass = new SubscriberPass();

        $subscriberPass->process($containerBuilder);
    }

    public function testDisableDoctrineEvents()
    {
        $containerBuilderMock = $this->getContainerBuilderMock();

        $containerBuilderMock
            ->expects($this->at(1))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.events'))
            ->will($this->returnValue(array()));
        $containerBuilderMock
            ->expects($this->at(2))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.doctrine_entities'))
            ->will($this->returnValue(false));
        $containerBuilderMock
            ->expects($this->at(3))
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('easy_audit.event_subscriber'))
            ->will($this->returnValue(array()));

        $subscriberPass = new SubscriberPass();
        $subscriberPass->process($containerBuilderMock);
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
        $containerBuilderMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilderMock->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo("xiidea.easy_audit.event_listener"))
            ->will($this->returnValue(true));

        return $containerBuilderMock;
    }
}
