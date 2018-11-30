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
use Symfony\Component\DependencyInjection\Reference;
use Xiidea\EasyAuditBundle\DependencyInjection\Compiler\LoggerFactoryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class LoggerFactoryPassTest extends TestCase
{
    public function testProcessWithoutLoggerFactoryDefinition()
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);

        $containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo('xiidea.easy_audit.logger_factory'))
            ->will($this->returnValue(false));
        $containerBuilder->expects($this->never())
            ->method('findTaggedServiceIds');

        $subscriberPass = new LoggerFactoryPass();
        $subscriberPass->process($containerBuilder);
    }

    public function testProcessWithLoggerFactoryDefinitions()
    {
        $definitionMock = $this->getDefinitionMock();
        $containerBuilderMock = $this->getContainerBuilderMock();

        $containerBuilderMock->expects($this->once())
            ->method('getDefinition')
            ->with($this->equalTo('xiidea.easy_audit.logger_factory'))
            ->will($this->returnValue($definitionMock));

        $loggerFactoryPass = new LoggerFactoryPass();
        $loggerFactoryPass->process($containerBuilderMock);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDefinitionMock()
    {
        $i = 0;

        $definitionMock = $this->getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock();

        $loggers = [
            ['addLogger', ['id', new Reference('id')]],
            ['addLogger', ['foo', new Reference('foo')]]
        ];

        $definitionMock
            ->expects($this->at($i++))
            ->method('getMethodCalls')
            ->will($this->returnValue([]));
        $definitionMock
            ->expects($this->at($i++))
            ->method('setMethodCalls')
            ->with($this->equalTo([]));
        $definitionMock
            ->expects($this->at($i++))
            ->method('addMethodCall')
            ->with($this->equalTo('addLogger'), $this->equalTo($loggers[0][1]));
        $definitionMock->expects($this->at($i++))
            ->method('addMethodCall')
            ->with($this->equalTo('addLogger'), $this->equalTo($loggers[1][1]));
        $definitionMock
            ->expects($this->at($i++))
            ->method('getMethodCalls')
            ->will($this->returnValue($loggers));
        $definitionMock
            ->expects($this->at($i))
            ->method('setMethodCalls')
            ->with($this->equalTo($loggers));

        return $definitionMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContainerBuilderMock()
    {
        $containerBuilderMock = $this->createMock(ContainerBuilder::class);

        $containerBuilderMock->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo('xiidea.easy_audit.logger_factory'))
            ->will($this->returnValue(true));

        $containerBuilderMock->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('easy_audit.logger'))
            ->will($this->returnValue(['id' => [[]], 'foo' => [[]]]));

        return $containerBuilderMock;
    }
}
