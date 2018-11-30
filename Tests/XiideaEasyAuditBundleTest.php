<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests;

use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\XiideaEasyAuditBundle;

class XiideaEasyAuditBundleTest extends TestCase
{
    public function testBuildInitializeBundleAddCompilerPass()
    {
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        //Expect compiler pass to be added
        $containerBuilder
            ->expects($this->at(0))
            ->method("addCompilerPass")
            ->with($this->isInstanceOf('Xiidea\EasyAuditBundle\DependencyInjection\Compiler\MonologLoggerPass'));
        $containerBuilder
            ->expects($this->at(1))
            ->method("addCompilerPass")
            ->with($this->isInstanceOf('Xiidea\EasyAuditBundle\DependencyInjection\Compiler\LoggerFactoryPass'));
        $containerBuilder
            ->expects($this->at(2))
            ->method("addCompilerPass")
            ->with($this->isInstanceOf('Xiidea\EasyAuditBundle\DependencyInjection\Compiler\SubscriberPass'));

        $bundle = new XiideaEasyAuditBundle();
        $bundle->build($containerBuilder);
    }
}