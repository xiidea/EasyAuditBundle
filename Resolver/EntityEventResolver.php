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

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\Event;
use Xiidea\EasyAuditBundle\Events\DoctrineEntityEvent;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;

/** Custom Event Resolver Example Class */
class EntityEventResolver implements ContainerAwareInterface, EventResolverInterface
{
    use ContainerAwareTrait;

    protected $eventShortName;

    /** @var  $event DoctrineEntityEvent */
    protected $event;

    protected $entity;

    protected $eventName;

    protected $identity = ['', ''];


    /**
     * @param Event|DoctrineEntityEvent $event
     * @param $eventName
     *
     * @return array
     */
    public function getEventLogInfo(Event $event, $eventName)
    {
        if (!$event instanceof DoctrineEntityEvent) {
            return null;
        }

        $this->initialize($event, $eventName);

        if ($this->isUpdateEvent() && null === $this->getChangeSets($this->entity)) {
            return null;
        }

        $reflectionClass = $this->getReflectionClassFromObject($this->entity);

        return array(
            'description' => $this->getDescription($reflectionClass->getShortName()),
            'type'        => $this->getEventType($reflectionClass->getShortName())
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
     * @param DoctrineEntityEvent $event
     * @param string $eventName
     */
    private function initialize(DoctrineEntityEvent $event, $eventName)
    {
        $this->eventShortName = null;
        $this->eventName = $eventName;
        $this->event = $event;
        $this->entity = $event->getLifecycleEventArgs()->getEntity();
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
        return $this->isUpdateEvent() ? $this->getUnitOfWork()->getEntityChangeSet($entity) : null;
    }

    protected function isUpdateEvent()
    {
        return $this->getEventShortName() == 'updated';
    }


    /**
     * @param string $typeName
     * @return string
     */
    protected function getEventType($typeName)
    {
        return $typeName . " " . $this->getEventShortName();
    }

    /**
     * @param string $shortName
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
     * @return \ReflectionClass
     */
    protected function getReflectionClassFromObject($object)
    {
        return new \ReflectionClass(get_class($object));
    }

    /**
     * @return \Doctrine\ORM\UnitOfWork
     */
    protected function getUnitOfWork()
    {
        return $this->container->get('doctrine')->getManager()->getUnitOfWork();
    }
}
