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
use Symfony\Component\DependencyInjection\Definition;
use Xiidea\EasyAuditBundle\DependencyInjection\Compiler\ResolverFactoryPass;
use Xiidea\EasyAuditBundle\Model\BaseAuditLog;
use Xiidea\EasyAuditBundle\Resolver\EventResolverFactory;

class ResolverFactoryPassTest extends TestCase
{
    public function testProcessWithoutResolverFactoryDefinition()
    {
        $containerBuilder = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_resolver_factory'))
            ->will($this->returnValue(false));
        $containerBuilder->expects($this->never())
            ->method('getDefinition');

        $resolverFactoryPass = new ResolverFactoryPass();
        $resolverFactoryPass->process($containerBuilder);
    }

    public function testProcessWithResolverFactoryDefinitions()
    {
        $definitionObject = $this->getDefinitionObject();
        $containerBuilderMock = $this->getContainerBuilderMock();

        $containerBuilderMock->expects($this->once())
            ->method('getDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_resolver_factory'))
            ->will($this->returnValue($definitionObject));

        $getParameterCall = array(
            array('xiidea.easy_audit.custom_resolvers', array()),
            array('xiidea.easy_audit.resolver', 'default.resolver'),
            array('xiidea.easy_audit.doctrine_event_resolver', null),
        );

        $containerBuilderMock->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValueMap($getParameterCall));

        $resolverFactoryPass = new ResolverFactoryPass();
        $resolverFactoryPass->process($containerBuilderMock);

        $methodCalls = $definitionObject->getMethodCalls();
        $this->assertEquals(5, count($methodCalls), 'Method call count should be updated');
        $this->assertEquals(array('setCommonResolver', array('default.resolver')), $methodCalls[0], 'Method call count should be updated');
        $this->assertEquals(array('setDebug', array(false)), $methodCalls[4], 'Method call count should be updated');
    }

    public function testWithDoctrineEntityResolverShouldRegistered()
    {
        $definitionObject = $this->getDefinitionObject();
        $containerBuilderMock = $this->getContainerBuilderMock();

        $containerBuilderMock->expects($this->once())
            ->method('getDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_resolver_factory'))
            ->will($this->returnValue($definitionObject));

        $getParameterCall = array(
            array('xiidea.easy_audit.custom_resolvers', array()),
            array('xiidea.easy_audit.resolver', 'default.resolver'),
            array('xiidea.easy_audit.doctrine_event_resolver', 'entity.resolver'),
        );

        $containerBuilderMock->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValueMap($getParameterCall));

        $resolverFactoryPass = new ResolverFactoryPass();
        $resolverFactoryPass->process($containerBuilderMock);

        $methodCalls = $definitionObject->getMethodCalls();
        $this->assertEquals(6, count($methodCalls), 'Method call count should be updated');
        $this->assertEquals(array('setCommonResolver', array('default.resolver')), $methodCalls[0], 'Method call count should be updated');
        $this->assertEquals(array('setDebug', array(false)), $methodCalls[5], 'Method call count should be updated');
    }

    public function testShouldRegisterCustomResolvers()
    {
        $hasDefinitionCall = array(
            array('xiidea.easy_audit.event_resolver_factory', true),
            array('resolver1', true),
        );

        $getParameterCall = array(
            array('xiidea.easy_audit.custom_resolvers', array('event1' => 'resolver1')),
            array('xiidea.easy_audit.resolver', 'default.resolver'),
            array('xiidea.easy_audit.doctrine_event_resolver', null),
        );

        $definitionObject = $this->getDefinitionObject();
        $containerBuilderMock = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilderMock->expects($this->any())
            ->method('hasDefinition')
            ->will($this->returnValueMap($hasDefinitionCall));

        $containerBuilderMock->expects($this->once())
            ->method('getDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_resolver_factory'))
            ->will($this->returnValue($definitionObject));

        $containerBuilderMock->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValueMap($getParameterCall));

        $resolverFactoryPass = new ResolverFactoryPass();
        $resolverFactoryPass->process($containerBuilderMock);

        $methodCalls = $definitionObject->getMethodCalls();
        $this->assertEquals(6, count($methodCalls), 'Method call count should be updated');
        $this->assertEquals(array('addCustomResolver', array('resolver1', $methodCalls[0][1][1])), $methodCalls[0], 'addCustomResolver called');
        $this->assertEquals(array('setCommonResolver', array('default.resolver')), $methodCalls[1], 'Method setCommonResolver added');
        $this->assertEquals(array('setDebug', array(false)), $methodCalls[5], 'Method call count should be updated');
    }

    /**
     * @return Definition
     */
    protected function getDefinitionObject()
    {
        $definition = new Definition(EventResolverFactory::class, array(array(), 'username', BaseAuditLog::class));

        return $definition
            ->addMethodCall('setAuthChecker', [null])
            ->addMethodCall('setRequestStack', [null])
            ->addMethodCall('setTokenStorage', [null])
            ->addMethodCall('setDebug', [false])
        ;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContainerBuilderMock()
    {
        $containerBuilderMock = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilderMock->expects($this->any())
            ->method('hasDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_resolver_factory'))
            ->will($this->returnValue(true));

        return $containerBuilderMock;
    }
}
