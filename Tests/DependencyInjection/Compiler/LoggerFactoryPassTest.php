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

class LoggerFactoryPassTest extends TestCase
{
    public function testProcessWithoutLoggerFactoryDefinition()
    {
        $containerBuilder = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

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
        $definitionMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $loggers = array(
            array('addLogger', array('id', new Reference('id'))),
            array('addLogger', array('foo', new Reference('foo'))),
        );

        $definitionMock
            ->expects($this->exactly(2))
            ->method('getMethodCalls')
            ->willReturnOnConsecutiveCalls(array(), $loggers);

        $definitionMock
            ->expects($this->exactly(2))
            ->method('setMethodCalls')
            ->withConsecutive(
                array($this->equalTo(array())),
                array($this->equalTo($loggers))
            );

        $definitionMock
            ->expects($this->exactly(2))
            ->method('addMethodCall')
            ->withConsecutive(
                array($this->equalTo('addLogger'), $this->equalTo($loggers[0][1])),
                array($this->equalTo('addLogger'), $this->equalTo($loggers[1][1]))
            );

        return $definitionMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContainerBuilderMock()
    {
        $containerBuilderMock = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilderMock->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo('xiidea.easy_audit.logger_factory'))
            ->will($this->returnValue(true));

        $containerBuilderMock->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('easy_audit.logger'))
            ->will($this->returnValue(array('id' => array(array()), 'foo' => array(array()))));
        return $containerBuilderMock;
    }
}
