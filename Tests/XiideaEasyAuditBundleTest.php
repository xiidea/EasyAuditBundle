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
            ->expects($this->exactly(4))
            ->method('addCompilerPass')
            ->withConsecutive(
                array($this->isInstanceOf('Xiidea\EasyAuditBundle\DependencyInjection\Compiler\MonologLoggerPass')),
                array($this->isInstanceOf('Xiidea\EasyAuditBundle\DependencyInjection\Compiler\LoggerFactoryPass')),
                array($this->isInstanceOf('Xiidea\EasyAuditBundle\DependencyInjection\Compiler\SubscriberPass')),
                array($this->isInstanceOf('Xiidea\EasyAuditBundle\DependencyInjection\Compiler\ResolverFactoryPass'))
            );

        $bundle = new XiideaEasyAuditBundle();
        $bundle->build($containerBuilder);
    }
}
