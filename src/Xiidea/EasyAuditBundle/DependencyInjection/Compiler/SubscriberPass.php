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

class SubscriberPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {

        if (false === $container->hasDefinition('xiidea.easy_audit.logger_factory')) {
            return;
        }

        $this->initializeLoggerFactory($container);

        if (false === $container->hasDefinition('xiidea.easy_audit.event_listener')) {
            return;
        }

        $this->initializeSubscriberEvents($container);
    }

    private function initializeLoggerFactory(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('xiidea.easy_audit.logger_factory');

        $calls = $definition->getMethodCalls();
        $definition->setMethodCalls(array());

        foreach ($container->findTaggedServiceIds('easy_audit.logger') as $id => $attributes) {
            $definition->addMethodCall('addLogger', array($id, new Reference($id)));
        }

        $definition->setMethodCalls(array_merge($definition->getMethodCalls(), $calls));
    }

    private function initializeSubscriberEvents(ContainerBuilder $container)
    {

        $definition = $container->getDefinition('xiidea.easy_audit.event_listener');
        $events = $container->getParameter('xiidea.easy_audit.events');
        $this->addDoctrineEvents($container, $events);

        foreach($events as $event){
            $definition->addTag('kernel.event_listener', array(
                'event' => $event,
                'method' => 'resolveEventHandler'
            ));
        }
    }

    private function addDoctrineEvents(ContainerBuilder $container, &$events = array())
    {
        $doctrine_entities = $container->getParameter('xiidea.easy_audit.doctrine_entities');

        if (empty($doctrine_entities)) {
            return;
        }

        foreach ($this->getDoctrineEventsList() as $constant) {
            array_push($events, $constant);
        }
    }

    private function getDoctrineEventsList()
    {
        $reflectionClass = new \ReflectionClass('Xiidea\EasyAuditBundle\Events\DoctrineEvents');
        return  $reflectionClass->getConstants();
    }
}
