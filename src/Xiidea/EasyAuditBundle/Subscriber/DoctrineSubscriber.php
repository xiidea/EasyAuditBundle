<?php
/*
 * This file is part of the EasyAudit package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Xiidea\EasyAuditBundle\Events\DoctrineEntityEvent;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;

class DoctrineSubscriber implements EventSubscriber
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;
    /**
     * @var array
     */
    private $entities;

    public function __construct(ContainerInterface $container, $entities = array())
    {
        $this->container = $container;
        $this->entities = $entities;
    }

    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
            'preRemove',
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        if (!$this->isConfiguredToTrack($args->getEntity(), 'postPersist')) {
            return;
        }

        $this->handleEvent(DoctrineEvents::ENTITY_CREATED, $args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->handleEvent(DoctrineEvents::ENTITY_UPDATED, $args);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $this->handleEvent(DoctrineEvents::ENTITY_DELETED, $args);
    }

    private function handleEvent($eventName, LifecycleEventArgs $args)
    {
        if (!$this->isConfiguredToTrack($args->getEntity(), $eventName)) {
            return;
        }

        $this->container->get('event_dispatcher')->dispatch($eventName,
            new DoctrineEntityEvent($this->container, $args)
        );
    }

    private function isConfiguredToTrack($entity, $eventName = '')
    {
        $class = get_class($entity);

        //Not configured
        if (!isset($this->entities[$class])) {
            return FALSE;
        }

        //Allowed to track all event
        if (empty($this->entities[$class])) {
            return TRUE;
        }

        $eventType = DoctrineEvents::getShortEventType($eventName);

        //Check if Allowed for $eventType event
        return (is_array($this->entities[$class]) && in_array($eventType, $this->entities[$class]));
    }
}