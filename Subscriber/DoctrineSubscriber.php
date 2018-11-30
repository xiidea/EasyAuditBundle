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
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Xiidea\EasyAuditBundle\Events\DoctrineDocumentEvent;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;
use Xiidea\EasyAuditBundle\Annotation\ODMSubscribedEvents;

class DoctrineSubscriber implements EventSubscriber
{
    use ContainerAwareTrait;

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

    public function __construct($entities = [])
    {
        $this->entities = $entities;
    }

    public function getSubscribedEvents()
    {
        return [
            'postPersist',
            'postUpdate',
            'preRemove',
            'postRemove'
        ];
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
        if (false === $this->isConfiguredToTrack($args->getDocument(), DoctrineEvents::ENTITY_DELETED)) {
            return;
        }

        $className = ClassUtils::getClass($args->getDocument());

        if (!isset($this->toBeDeleted[$className])) {
            $this->toBeDeleted[$className] = [];
        }

        $this->toBeDeleted[$className][spl_object_hash($args->getDocument())] = $this->getIddocument($args, $className);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $identity = $this->getToBeDeletedId($args->getDocument());

        if (null !== $identity) {
            $this->dispatcher->dispatch(
                DoctrineEvents::ENTITY_DELETED,
                new DoctrineDocumentEvent($args, $identity)
            );
        }
    }

    private function getToBeDeletedId($document)
    {
        if ($this->isScheduledForDelete($document)) {
            return $this->toBeDeleted[ClassUtils::getClass($document)][spl_object_hash($document)];
        }

        return null;
    }

    /**
     * @param string $eventName
     * @param LifecycleEventArgs $args
     */
    private function handleEvent($eventName, LifecycleEventArgs $args)
    {
        if (true === $this->isConfiguredToTrack($args->getDocument(), $eventName)) {
            $this->dispatcher->dispatch(
                $eventName,
                new DoctrineDocumentEvent(
                    $args, $this->getIddocument($args, ClassUtils::getClass($args->getDocument()))
                )
            );
        }
    }

    /**
     * @param $document
     * @param string $eventName
     * @return bool
     */
    private function isConfiguredToTrack($document, $eventName = '')
    {
        $class = ClassUtils::getClass($document);
        $eventType = DoctrineEvents::getShortEventType($eventName);

        if (null !== $track = $this->isAnnotatedEvent($document, $eventType)) {
            return $track;
        }

        if (!$this->isConfigured($class)) {
            return false;
        }

        if ($this->shouldTrackAllEventType($class)) {
            return true;
        }

        return $this->shouldTrackEventType($eventType, $class);
    }

    /**
     * @param $document
     * @param string $eventType
     * @return bool|null
     */
    protected function isAnnotatedEvent($document, $eventType)
    {
        $metaData = $this->hasAnnotation($document);

        if (!$metaData) {
            return null;
        }

        return empty($metaData->events) || in_array($eventType, $metaData->events, true);
    }

    /**
     * @param $document
     * @return null|object
     */
    protected function hasAnnotation($document)
    {
        $reflection = $this->getReflectionClassFromObject($document);

        return $this
            ->getAnnotationReader()
            ->getClassAnnotation($reflection, ODMSubscribedEvents::class);
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
        return (is_array($this->entities[$class]) && in_array($eventType, $this->entities[$class], true));
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
    protected function getIddocument(LifecycleEventArgs $args, $className)
    {
        return $args->getDocumentManager()->getClassMetadata($className)->getIdentifierValues($args->getDocument());
    }

    /**
     * @param $document
     * @return boolean
     */
    private function isScheduledForDelete($document)
    {
        $originalClassName = ClassUtils::getClass($document);

        return isset($this->toBeDeleted[$originalClassName]) && isset(
                $this->toBeDeleted[$originalClassName][spl_object_hash(
                    $document
                )]
            );
    }

    /**
     * @param EventDispatcher $dispatcher
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
}
