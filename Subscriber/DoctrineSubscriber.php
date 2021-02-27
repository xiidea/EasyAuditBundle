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
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Xiidea\EasyAuditBundle\Annotation\SubscribeDoctrineEvents;
use Xiidea\EasyAuditBundle\Events\DoctrineObjectEvent;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;

class DoctrineSubscriber implements EventSubscriber
{
    /** @var \Doctrine\Common\Annotations\Reader */
    private $annotationReader;

    private $toBeDeleted = [];

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

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
            'postRemove',
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->handleEvent(DoctrineEvents::ENTITY_CREATED, $args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->handleEvent(DoctrineEvents::ENTITY_UPDATED, $args);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        if (false === $this->isConfiguredToTrack($args->getObject(), DoctrineEvents::ENTITY_DELETED)) {
            return;
        }

        $className = ClassUtils::getClass($args->getObject());

        if (!isset($this->toBeDeleted[$className])) {
            $this->toBeDeleted[$className] = [];
        }

        $this->toBeDeleted[$className][spl_object_hash($args->getObject())] = $this->getIdentity($args, $className);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $identity = $this->getToBeDeletedId($args->getObject());

        if (null !== $identity) {
            $this->dispatcher->dispatch(new DoctrineObjectEvent($args, $identity), DoctrineEvents::ENTITY_DELETED);
        }
    }

    private function getToBeDeletedId($entity)
    {
        if ($this->isScheduledForDelete($entity)) {
            return $this->toBeDeleted[ClassUtils::getClass($entity)][spl_object_hash($entity)];
        }

        return null;
    }

    /**
     * @param string             $eventName
     * @param LifecycleEventArgs $args
     */
    private function handleEvent($eventName, LifecycleEventArgs $args)
    {
        if (true === $this->isConfiguredToTrack($args->getObject(), $eventName)) {
            $this->dispatcher->dispatch(new DoctrineObjectEvent($args, $this->getIdentity($args, ClassUtils::getClass($args->getObject()))),
                $eventName
            );
        }
    }

    /**
     * @param $entity
     * @param string $eventName
     *
     * @return bool
     */
    private function isConfiguredToTrack($entity, $eventName = '')
    {
        $class = ClassUtils::getClass($entity);
        $eventType = DoctrineEvents::getShortEventType($eventName);

        if (null !== $track = $this->isAnnotatedEvent($entity, $eventType)) {
            return $track;
        }

        if (!isset($this->entities[$class])) {
            return false;
        }

        if ($this->shouldTrackAllEventType($class)) {
            return true;
        }

        return $this->shouldTrackEventType($eventType, $class);
    }

    /**
     * @param $entity
     * @param string $eventType
     *
     * @return bool|null
     */
    protected function isAnnotatedEvent($entity, $eventType)
    {
        $metaData = $this->hasAnnotation($entity);

        if (!$metaData) {
            return null;
        }

        return empty($metaData->events) || in_array($eventType, $metaData->events);
    }

    /**
     * @param $entity
     *
     * @return null|object
     */
    protected function hasAnnotation($entity)
    {
        $reflection = $this->getReflectionClassFromObject($entity);

        return $this
            ->getAnnotationReader()
            ->getClassAnnotation($reflection, SubscribeDoctrineEvents::class);
    }

    /**
     * @return \Doctrine\Common\Annotations\Reader
     */
    protected function getAnnotationReader()
    {
        return $this->annotationReader;
    }

    /**
     * @param $object
     *
     * @return \ReflectionClass
     */
    protected function getReflectionClassFromObject($object)
    {
        $class = ClassUtils::getClass($object);

        return new \ReflectionClass($class);
    }

    /**
     * @param string $eventType
     * @param string $class
     *
     * @return bool
     */
    private function shouldTrackEventType($eventType, $class)
    {
        return is_array($this->entities[$class]) && in_array($eventType, $this->entities[$class]);
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    private function shouldTrackAllEventType($class)
    {
        return empty($this->entities[$class]);
    }

    /**
     * @param \Doctrine\Common\Annotations\Reader $annotationReader
     */
    public function setAnnotationReader($annotationReader = null)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @param LifecycleEventArgs $args
     * @param $className
     *
     * @return array
     */
    protected function getIdentity(LifecycleEventArgs $args, $className)
    {
        return $args->getObjectManager()->getClassMetadata($className)->getIdentifierValues($args->getObject());
    }

    /**
     * @param $entity
     *
     * @return bool
     */
    private function isScheduledForDelete($entity)
    {
        $originalClassName = ClassUtils::getClass($entity);

        return isset($this->toBeDeleted[$originalClassName]) && isset($this->toBeDeleted[$originalClassName][spl_object_hash($entity)]);
    }

    /**
     * @param EventDispatcher $dispatcher
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
}
