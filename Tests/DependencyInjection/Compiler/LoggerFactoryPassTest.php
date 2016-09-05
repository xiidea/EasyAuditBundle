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
use Xiidea\EasyAuditBundle\DependencyInjection\Compiler\LoggerFactoryPass;

class LoggerFactoryPassTest extends \PHPUnit_Framework_TestCase {

    public function testProcessWithoutLoggerFactoryDefinition()
    {
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo("xiidea.easy_audit.logger_factory"))
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

        $definitionMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $loggers = array(
            array('addLogger', array('id', new Reference('id'))),
            array('addLogger', array('foo', new Reference('foo')))
        );

        $definitionMock
            ->expects($this->at($i++))
            ->method('getMethodCalls')
            ->will($this->returnValue(array()));
        $definitionMock
            ->expects($this->at($i++))
            ->method('setMethodCalls')
            ->with($this->equalTo(array()));
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
        $containerBuilderMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilderMock->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo("xiidea.easy_audit.logger_factory"))
            ->will($this->returnValue(true));

        $containerBuilderMock->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('easy_audit.logger'))
            ->will($this->returnValue(array('id' => array(array()), 'foo' => array(array()))));
        return $containerBuilderMock;
    }
}
