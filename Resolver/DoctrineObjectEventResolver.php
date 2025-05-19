<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Resolver;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\EventDispatcher\Event;
use Xiidea\EasyAuditBundle\Common\ClassUtils;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;
use Xiidea\EasyAuditBundle\Events\DoctrineObjectEvent;

/** Custom Event Resolver Example Class */
class DoctrineObjectEventResolver implements EventResolverInterface
{
    protected $eventShortName;

    /** @var $event DoctrineObjectEvent */
    protected $event;

    protected $entity;

    protected $eventName;

    protected $identity = ['', ''];

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    protected $changeSetGetterMethods = [
        'getEntityChangeSet',
        'getDocumentChangeSet',
    ];

    /**
     * @param Event|DoctrineObjectEvent $event
     * @param $eventName
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    #[\Override]
    public function getEventLogInfo(Event $event, $eventName)
    {
        if (!$event instanceof DoctrineObjectEvent) {
            return null;
        }

        $this->initialize($event, $eventName);

        if ($this->isUpdateEvent() && null === $this->getChangeSets($this->entity)) {
            return null;
        }

        $reflectionClass = $this->getReflectionClassFromObject($this->entity);

        return array(
            'description' => $this->getDescription($reflectionClass->getShortName()),
            'type' => $this->getEventType($reflectionClass->getShortName()),
        );
    }

    protected function getSingleIdentity()
    {
        foreach ($this->event->getIdentity() as $field => $value) {
            return [$field, $value];
        }

        return ['', ''];
    }

    /**
     * @param DoctrineObjectEvent $event
     * @param string $eventName
     */
    private function initialize(DoctrineObjectEvent $event, $eventName)
    {
        $this->eventShortName = null;
        $this->eventName = $eventName;
        $this->event = $event;
        $this->entity = $event->getLifecycleEventArgs()->getObject();
        $this->identity = $this->getSingleIdentity();
    }

    private function getIdField()
    {
        return $this->identity[0];
    }

    private function getIdValue()
    {
        return $this->identity[1];
    }

    protected function getChangeSets($entity)
    {
        $unitOfWork = $this->getUnitOfWork();
        foreach ($this->changeSetGetterMethods as $method) {
            $getter = [$unitOfWork, $method];
            if (is_callable($getter)) {
                return call_user_func($getter, $entity);
            }
        }

        return null;
    }

    protected function isUpdateEvent()
    {
        return 'updated' === $this->getEventShortName();
    }

    /**
     * @param string $typeName
     *
     * @return string
     */
    protected function getEventType($typeName)
    {
        return $typeName.' '.$this->getEventShortName();
    }

    /**
     * @param string $shortName
     *
     * @return string
     */
    protected function getDescription($shortName)
    {
        return sprintf(
            '%s has been %s with %s = "%s"',
            $shortName,
            $this->getEventShortName(),
            $this->getIdField(),
            $this->getIdValue()
        );
    }

    /**
     * @return string
     */
    protected function getEventShortName()
    {
        if (null === $this->eventShortName) {
            $this->eventShortName = DoctrineEvents::getShortEventType($this->getName());
        }

        return $this->eventShortName;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return $this->eventName;
    }

    /**
     * @param $object
     *
     * @return \ReflectionClass
     *
     * @throws \ReflectionException
     */
    protected function getReflectionClassFromObject($object)
    {
        return new \ReflectionClass(ClassUtils::getClass($object));
    }

    /**
     * @return \Doctrine\ODM\MongoDB\UnitOfWork|\Doctrine\ORM\UnitOfWork
     */
    protected function getUnitOfWork()
    {
        return $this->getDoctrine()->getManager()->getUnitOfWork();
    }

    /**
     * @return ManagerRegistry|object
     */
    protected function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @param ManagerRegistry $doctrine
     */
    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
    }
}
