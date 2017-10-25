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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Xiidea\EasyAuditBundle\DependencyInjection\Compiler\MonologLoggerPass;

class MonologLoggerPassTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder */
    private $containerBuilder;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Definition */
    private $definition;

    public function setUp()
    {
        $this->containerBuilder = $this->createMock(ContainerBuilder::class);
        $this->definition = $this->createMock(Definition::class);
    }

    public function testProcessWithoutLoggerDefinition()
    {
        $this->containerBuilder ->expects($this->once())
            ->method('hasAlias')
            ->with($this->equalTo("logger"))
            ->will($this->returnValue(false));
        $this->containerBuilder ->expects($this->never())
            ->method('getDefinition');

        $subscriberPass = new MonologLoggerPass();
        $subscriberPass->process( $this->containerBuilder);
    }

    public function testProcessWithLoggerDefinitions()
    {
        $this->definition->expects($this->once())
            ->method('setPublic')
            ->with($this->equalTo(TRUE));

        $this->containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with($this->equalTo('xiidea.easy_audit.mono_logger.service'))
            ->will($this->returnValue( $this->definition));

        $loggerFactoryPass = new MonologLoggerPass();
        $loggerFactoryPass->process( $this->containerBuilder);
    }
}
