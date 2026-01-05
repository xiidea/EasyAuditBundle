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

use Xiidea\EasyAuditBundle\Common\ClassUtils;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xiidea\EasyAuditBundle\Attribute\SubscribeDoctrineEvents;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;
use Xiidea\EasyAuditBundle\Events\DoctrineObjectEvent;

class DoctrineSubscriber
{
    private array $toBeDeleted = [];
    private  $dispatcher = null;

    public function __construct(private array $entities = [])
    {
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->handleEvent(DoctrineEvents::ENTITY_CREATED, $args);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->handleEvent(DoctrineEvents::ENTITY_UPDATED, $args);
    }

    public function preRemove(LifecycleEventArgs $args): void
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

    public function postRemove(LifecycleEventArgs $args): void
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
     * @param  string  $eventName
     * @param  LifecycleEventArgs  $args
     */
    private function handleEvent($eventName, LifecycleEventArgs $args): void
    {
        if (true === $this->isConfiguredToTrack($args->getObject(), $eventName)) {
            $this->dispatcher->dispatch(
                new DoctrineObjectEvent($args, $this->getIdentity($args, ClassUtils::getClass($args->getObject()))),
                $eventName
            );
        }
    }

    /**
     * @param $entity
     * @param  string  $eventName
     *
     * @return bool
     */
    private function isConfiguredToTrack($entity, $eventName = ''): ?bool
    {
        $class = ClassUtils::getClass($entity);
        $eventType = DoctrineEvents::getShortEventType($eventName);

        if (null !== $track = $this->isAttributedEvent($entity, $eventType)) {
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
     * @return bool|null
     * @throws \ReflectionException
     */
    protected function isAttributedEvent($entity, $eventType)
    {
        $reflection = new \ReflectionClass($entity);
        $attribute = $reflection->getAttributes(SubscribeDoctrineEvents::class);
        if (empty($attribute)) {
            return null;
        }

        $instance = $attribute[0]->newInstance();

        return empty($instance->events) || in_array($eventType, $instance->events);
    }


    /**
     * @param  string  $eventType
     * @param  string  $class
     *
     * @return bool
     */
    private function shouldTrackEventType($eventType, $class)
    {
        return is_array($this->entities[$class]) && in_array($eventType, $this->entities[$class]);
    }

    /**
     * @param  string  $class
     *
     * @return bool
     */
    private function shouldTrackAllEventType($class)
    {
        return empty($this->entities[$class]);
    }

    /**
     * @param  LifecycleEventArgs  $args
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

        return isset($this->toBeDeleted[$originalClassName]) && isset(
                $this->toBeDeleted[$originalClassName][spl_object_hash(
                    $entity
                )]
            );
    }

    /**
     * @param  EventDispatcherInterface  $dispatcher
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
}
