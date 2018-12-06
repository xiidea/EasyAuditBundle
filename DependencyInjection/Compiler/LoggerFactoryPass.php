<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class LoggerFactoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('xiidea.easy_audit.logger_factory')) {
            return;
        }

        $definition = $container->getDefinition('xiidea.easy_audit.logger_factory');

        $calls = $definition->getMethodCalls();
        $definition->setMethodCalls(array());

        foreach ($container->findTaggedServiceIds('easy_audit.logger') as $id => $attributes) {
            $definition->addMethodCall('addLogger', array($id, new Reference($id)));
        }

        $definition->setMethodCalls(array_merge($definition->getMethodCalls(), $calls));
    }
}
