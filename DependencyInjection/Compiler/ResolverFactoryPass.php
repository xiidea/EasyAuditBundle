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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ResolverFactoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('xiidea.easy_audit.event_resolver_factory')) {
            return;
        }

        $definition = $container->getDefinition('xiidea.easy_audit.event_resolver_factory');

        $calls = $definition->getMethodCalls();
        $definition->setMethodCalls([]);

        foreach ($container->getParameter('xiidea.easy_audit.custom_resolvers') as $id) {
            if ($container->hasDefinition($id)) {
                $definition->addMethodCall('addCustomResolver', [$id, new Reference($id)]);
            }
        }

        $definition->addMethodCall(
            'setCommonResolver',
            [
                $this->getServiceReferenceByConfigName($container, 'resolver')
            ]
        );

        if ($container->has('doctrine_mongodb')) {
            $definition->addMethodCall(
                'setDocumentEventResolver',
                [
                    $this->getServiceReferenceByConfigName($container, 'document_event_resolver')
                ]
            );
        }

        $definition->setMethodCalls(array_merge($definition->getMethodCalls(), $calls));
    }

    /**
     * @param ContainerBuilder $container
     * @param $configName
     * @return Reference
     */
    protected function getServiceReferenceByConfigName(ContainerBuilder $container, $configName)
    {
        return new Reference($container->getParameter('xiidea.easy_audit.' . $configName));
    }
}