<?php
/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerAware;
use Xiidea\EasyAuditBundle\Events\DoctrineEntityEvent;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;

class DoctrineSubscriber extends ContainerAware implements EventSubscriber
{
    /**
     * @var array
     */
    private $entities;

    public function __construct($entities = array())
    {
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
            new DoctrineEntityEvent($args)
        );
    }

    private function isConfiguredToTrack($entity, $eventName = '')
    {
        $class = get_class($entity);
        $eventType = DoctrineEvents::getShortEventType($eventName);

        if(null !== $track = $this->isAnnotatedEvent($entity, $eventType)){
            return $track;
        }

        //Not configured
        if (!isset($this->entities[$class])) {
            return FALSE;
        }

        //Allowed to track all event
        if (empty($this->entities[$class])) {
            return TRUE;
        }

        //Check if Allowed for $eventType event
        return (is_array($this->entities[$class]) && in_array($eventType, $this->entities[$class]));
    }

    protected function isAnnotatedEvent($entity, $eventType)
    {
        $metaData = $this->hasAnnotation($entity);

        if (!$metaData) {
            return null;
        }

        return empty($metaData->events) || in_array($eventType, $metaData->events);
    }

    protected function hasAnnotation($entity)
    {
        $reflection = $this->getReflectionClassFromObject($entity);

        return $this
            ->getAnnotationReader()
            ->getClassAnnotation($reflection, 'Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents');

    }

    /**
     * @return \Doctrine\Common\Annotations\FileCacheReader
     */
    protected function getAnnotationReader()
    {
        return $this->container->get('annotation_reader');
    }

    protected function getReflectionClassFromObject($object)
    {
        $class = get_class($object);

        return new \ReflectionClass($class);
    }
}