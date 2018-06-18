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
use \Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Xiidea\EasyAuditBundle\Events\DoctrineEntityEvent;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;

class DoctrineSubscriber implements ContainerAwareInterface, EventSubscriber
{
    use ContainerAwareTrait;

    /** @var \Doctrine\Common\Annotations\Reader */
    private $annotationReader;

    private $toBeDeleted = [];

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
            'postRemove'
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
        if (false === $this->isConfiguredToTrack($args->getEntity(), DoctrineEvents::ENTITY_DELETED)) {
            return;
        }

        $className = ClassUtils::getClass($args->getEntity());

        if (!isset($this->toBeDeleted[$className])) {
            $this->toBeDeleted[$className] = [];
        }

        $this->toBeDeleted[$className][spl_object_hash($args->getEntity())] = $this->getIdentity($args, $className);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $identity = $this->getToBeDeletedId($args->getEntity());

        if ($identity) {
            $this->container->get('event_dispatcher')->dispatch(DoctrineEvents::ENTITY_DELETED,
                new DoctrineEntityEvent($args, $identity)
            );
        }
    }

    private function getToBeDeletedId($entity)
    {
        try{
            return $this->toBeDeleted[ClassUtils::getClass($entity)][spl_object_hash($entity)];
        }catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $eventName
     * @param LifecycleEventArgs $args
     */
    private function handleEvent($eventName, LifecycleEventArgs $args)
    {
        if (true === $this->isConfiguredToTrack($args->getEntity(), $eventName)) {
            $this->container->get('event_dispatcher')->dispatch($eventName,
                new DoctrineEntityEvent($args, $this->getIdentity($args, ClassUtils::getClass($args->getEntity())))
            );
        }
    }

    /**
     * @param $entity
     * @param string $eventName
     * @return bool
     */
    private function isConfiguredToTrack($entity, $eventName = '')
    {
        $class = ClassUtils::getClass($entity);
        $eventType = DoctrineEvents::getShortEventType($eventName);

        if (null !== $track = $this->isAnnotatedEvent($entity, $eventType)) {
            return $track;
        }

        if (!$this->isConfigured($class)) {
            return FALSE;
        }

        if ($this->shouldTrackAllEventType($class)) {
            return TRUE;
        }

        return $this->shouldTrackEventType($eventType, $class);
    }

    /**
     * @param $entity
     * @param string $eventType
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
     * @return null|object
     */
    protected function hasAnnotation($entity)
    {
        $reflection = $this->getReflectionClassFromObject($entity);

        return $this
            ->getAnnotationReader()
            ->getClassAnnotation($reflection, 'Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents');

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
     * @return bool
     */
    private function shouldTrackEventType($eventType, $class)
    {
        return (is_array($this->entities[$class]) && in_array($eventType, $this->entities[$class]));
    }

    /**
     * @param string $class
     * @return bool
     */
    private function shouldTrackAllEventType($class)
    {
        return empty($this->entities[$class]);
    }

    /**
     * @param string $class
     * @return bool
     */
    protected function isConfigured($class)
    {
        return isset($this->entities[$class]);
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
     * @return array
     */
    protected function getIdentity(LifecycleEventArgs $args, $className)
    {
        return $args->getEntityManager()->getClassMetadata($className)->getIdentifierValues($args->getEntity());
    }
}
