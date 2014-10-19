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
}
