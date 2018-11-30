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
use Xiidea\EasyAuditBundle\Document\BaseAuditLog;
use Xiidea\EasyAuditBundle\Resolver\EventResolverFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResolverFactoryPassTest extends TestCase
{
    public function testProcessWithoutResolverFactoryDefinition()
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);

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

        $getParameterCall = [
            ['xiidea.easy_audit.custom_resolvers', []],
            ['xiidea.easy_audit.resolver', 'default.resolver'],
        ];

        $containerBuilderMock->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValueMap($getParameterCall));

        $resolverFactoryPass = new ResolverFactoryPass();
        $resolverFactoryPass->process($containerBuilderMock);

        $methodCalls = $definitionObject->getMethodCalls();
        $this->assertCount(5, $methodCalls, 'Method call count should be updated');
        $this->assertEquals(
            ['setCommonResolver', ['default.resolver']],
            $methodCalls[0],
            'Method call count should be updated'
        );
        $this->assertEquals(['setDebug', [false]], $methodCalls[4], 'Method call count should be updated');
    }

    public function testWithDoctrineDocumentResolverShouldRegistered()
    {
        $definitionObject = $this->getDefinitionObject();
        $containerBuilderMock = $this->getContainerBuilderMock();

        $containerBuilderMock->expects($this->once())
            ->method('getDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_resolver_factory'))
            ->will($this->returnValue($definitionObject));

        $containerBuilderMock->expects($this->once())
            ->method('hasAlias')
            ->with($this->equalTo('doctrine_mongodb'))
            ->will($this->returnValue(true));

        $getParameterCall = [
            ['xiidea.easy_audit.custom_resolvers', []],
            ['xiidea.easy_audit.resolver', 'default.resolver'],
            ['xiidea.easy_audit.document_event_resolver', 'document.resolver'],
        ];

        $containerBuilderMock->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValueMap($getParameterCall));

        $resolverFactoryPass = new ResolverFactoryPass();
        $resolverFactoryPass->process($containerBuilderMock);

        $methodCalls = $definitionObject->getMethodCalls();
        $this->assertCount(6, $methodCalls, 'Method call count should be updated');
        $this->assertEquals(
            ['setCommonResolver', ['default.resolver']],
            $methodCalls[0],
            'Method call count should be updated'
        );
        $this->assertEquals(['setDebug', [false]], $methodCalls[5], 'Method call count should be updated');
    }

    public function testShouldRegisterCustomResolvers()
    {
        $hasDefinitionCall = [
            ['xiidea.easy_audit.event_resolver_factory', true],
            ['resolver1', true],
        ];

        $getParameterCall = [
            ['xiidea.easy_audit.custom_resolvers', ['event1' => 'resolver1']],
            ['xiidea.easy_audit.resolver', 'default.resolver'],
        ];

        $definitionObject = $this->getDefinitionObject();
        $containerBuilderMock = $this->createMock(ContainerBuilder::class);

        $containerBuilderMock->expects($this->any())
            ->method('hasDefinition')
            ->will($this->returnValueMap($hasDefinitionCall));

        $containerBuilderMock->expects($this->once())
            ->method('getDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_resolver_factory'))
            ->will($this->returnValue($definitionObject));

        $containerBuilderMock->expects($this->once())
            ->method('hasAlias')
            ->with($this->equalTo('doctrine_mongodb'))
            ->will($this->returnValue(false));

        $containerBuilderMock->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValueMap($getParameterCall));

        $resolverFactoryPass = new ResolverFactoryPass();
        $resolverFactoryPass->process($containerBuilderMock);

        $methodCalls = $definitionObject->getMethodCalls();
        $this->assertCount(6, $methodCalls, 'Method call count should be updated');
        $this->assertEquals(
            ['addCustomResolver', ['resolver1', $methodCalls[0][1][1]]],
            $methodCalls[0],
            'addCustomResolver called'
        );
        $this->assertEquals(
            ['setCommonResolver', ['default.resolver']],
            $methodCalls[1],
            'Method setCommonResolver added'
        );
        $this->assertEquals(['setDebug', [false]], $methodCalls[5], 'Method call count should be updated');
    }

    /**
     * @return Definition
     */
    protected function getDefinitionObject()
    {
        $definition = new Definition(EventResolverFactory::class, [[], 'username', BaseAuditLog::class]);

        return $definition
            ->addMethodCall('setAuthChecker', [null])
            ->addMethodCall('setRequestStack', [null])
            ->addMethodCall('setTokenStorage', [null])
            ->addMethodCall('setDebug', [false]);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContainerBuilderMock()
    {
        $containerBuilderMock = $this->createMock(ContainerBuilder::class);

        $containerBuilderMock->expects($this->any())
            ->method('hasDefinition')
            ->with($this->equalTo('xiidea.easy_audit.event_resolver_factory'))
            ->will($this->returnValue(true));

        return $containerBuilderMock;
    }
}
