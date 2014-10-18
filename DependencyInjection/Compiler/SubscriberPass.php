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

        $eventsList = $container->getParameter('xiidea.easy_audit.events');

        $this->appendDoctrineEventsToList($container, $eventsList);
        $this->appendSubscribedEventsToList($container, $eventsList);

        $this->registerEventsToListener($eventsList, $container);

    }

    private function appendDoctrineEventsToList(ContainerBuilder $container, &$events = array())
    {
        $doctrine_entities = $container->getParameter('xiidea.easy_audit.doctrine_entities');

        if ($doctrine_entities === false) {
            return;
        }

        foreach ($this->getDoctrineEventsList() as $constant) {
            array_push($events, $constant);
        }
    }

    private function getDoctrineEventsList()
    {
        $reflectionClass = new \ReflectionClass('Xiidea\EasyAuditBundle\Events\DoctrineEvents');
        return $reflectionClass->getConstants();
    }

    /**
     * @param ContainerBuilder $container
     * @param array $events
     */
    private function appendSubscribedEventsToList(ContainerBuilder $container, &$events = array())
    {
        $taggedSubscribers = $container->findTaggedServiceIds('easy_audit.event_subscriber');


        if (!empty($taggedSubscribers)) {

            foreach ($taggedSubscribers as $id => $attributes) {
                $this->appendEventsFromSubscriber($container, $events, $id, @$attributes[0]['resolver']);
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param $events
     * @param $id
     * @param $defaultResolver
     */
    private function appendEventsFromSubscriber(ContainerBuilder $container, &$events, $id, $defaultResolver = null)
    {
        $subscriber = $container->get($id);

        $subscribedEvents = $subscriber->getSubscribedEvents();

        foreach ($subscribedEvents as $key => $item) {

            $resolver = !empty($defaultResolver) && !is_string($key) ? $defaultResolver : $key;
            $this->addEventFromSubscriber($events, $item, $resolver);
        }
    }

    /**
     * @param $events
     * @param $item
     * @param $resolver
     */
    private function addEventFromSubscriber(&$events, $item, $resolver)
    {
        $items = array($item);
        foreach ($items as $value) {
            $this->appendEventArray($events, $resolver, $value);
        }
    }

    /**
     * @param $events
     * @param $resolver
     * @param $item
     */
    protected function appendEventArray(&$events, $resolver, $item)
    {
        if ($this->isEventWithResolver($resolver)) {
            $this->appendEventWithResolver($events, $item, $resolver);
            return;
        }

        array_push($events, $item);
    }

    /**
     * @param $resolver
     * @return bool
     */
    protected function isEventWithResolver($resolver)
    {
        return is_string($resolver);
    }

    /**
     * @param $events
     * @param $items
     * @param $key
     * @internal param $event
     */
    protected function appendEventWithResolver(&$events, $items, $key)
    {
        $items = (array) $items;

        foreach($items as $item) {
            array_push($events, array($item => $key));
        }
    }

    /**
     * @param $events
     * @param ContainerBuilder $container
     */
    protected function registerEventsToListener($events, ContainerBuilder $container)
    {
        $definition = $container->getDefinition('xiidea.easy_audit.event_listener');
        $customResolvers = $container->getParameter('xiidea.easy_audit.custom_resolvers');

        $listenableEventsList = $this->getListenableEventList($events);
        $this->buildCustomResolverList($events, $customResolvers);

        $definition->setTags(array('kernel.event_listener' => array_values($listenableEventsList)));
        $container->setParameter('xiidea.easy_audit.custom_resolvers', $customResolvers);
    }

    /**
     * @param $events
     * @return array
     */
    protected function getListenableEventList($events)
    {
        $eventList = array();

        foreach ($events as $item) {

            $event = is_array($item) ? key($item) : $item;

            $eventList[$event] = array(
                'event' => $event,
                'method' => 'resolveEventHandler'
            );
        }

        return $eventList;
    }

    private function buildCustomResolverList($events, &$customResolver)
    {
        foreach ($events as $item) {
            if (is_array($item)) {
                $event = key($item);
                $resolver = $item[$event];
                $customResolver[$event] = $resolver;
            }
        }
    }
}
