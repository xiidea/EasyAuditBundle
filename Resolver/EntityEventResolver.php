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

use Symfony\Component\DependencyInjection\ContainerAware;
use Xiidea\EasyAuditBundle\Events\DoctrineEntityEvent;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;
use Symfony\Component\EventDispatcher\Event;

/** Custom Event Resolver Example Class */
class EntityEventResolver extends ContainerAware implements EventResolverInterface
{
    protected $candidateProperties = array('name', 'title');

    protected $propertiesFound = array();

    protected $eventShortName;

    /** @var  $event DoctrineEntityEvent */
    protected $event;

    protected $entity;

    protected $eventName;


    /**
     * @param Event|DoctrineEntityEvent $event
     *
     * @param $eventName
     * @internal param $Event
     *
     * @return array
     */
    public function getEventLogInfo(Event $event, $eventName)
    {
        if (!$event instanceof DoctrineEntityEvent) {
            return null;
        }

        $this->eventName = $eventName;
        $this->event = $event;
        $this->entity = $event->getLifecycleEventArgs()->getEntity();

        if ($this->isUpdateEvent() && null === $this->getChangeSets($this->entity)) {
            return null;
        }


        $entityClass = $this->getReflectionClassFromObject($this->entity);

        return array(
            'description' => $this->getDescription($entityClass),
            'type' => $this->getEventType($entityClass->getShortName()),
        );

    }

    protected function getChangeSets($entity)
    {
        if ($this->isUpdateEvent()) {
            return $this->getUnitOfWork()->getEntityChangeSet($entity);
        }

        return null;
    }

    protected function isUpdateEvent()
    {
        return $this->getEventShortName() == 'updated';
    }

    /**
     * @return mixed
     */
    protected function getEntity()
    {
        if (!$this->entity) {
            $this->entity = $this->event->getLifecycleEventArgs()->getEntity();
        }

        return $this->entity;
    }

    protected function getProperty($name)
    {
        $propertyGetter = 'get' . $this->propertiesFound[$name];

        if(!is_callable(array($this->entity, $propertyGetter))) {
            return "";
        }

        return $this->entity->$propertyGetter();
    }

    protected function getEventType($typeName)
    {
        return $typeName . " " . $this->getEventShortName();
    }

    protected function getDescription(\ReflectionClass $reflectionClass)
    {
        $property = $this->getBestCandidatePropertyForIdentify($reflectionClass);

        $descriptionTemplate = '%s has been %s';

        if ($property) {
            $descriptionTemplate .= sprintf(' with %s = "%s"', $property, $this->getProperty($property));
        }

        return sprintf(
            $descriptionTemplate,
            $reflectionClass->getShortName(),
            $this->getEventShortName()
        );
    }

    protected function getEventShortName()
    {
        if (!$this->eventShortName) {
            $this->eventShortName = DoctrineEvents::getShortEventType($this->getName());
        }

        return $this->eventShortName;
    }

    protected function getBestCandidatePropertyForIdentify(\ReflectionClass $reflectionClass)
    {
        $properties = $reflectionClass->getProperties(
            \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE
        );

        $propertyName = null;

        $entityIdStr = strtolower($reflectionClass->getShortName()) . "id";

        foreach ($properties as $property) {
            $propertyNameLower = strtolower($property->name);

            if (null !== $foundPropertyName = $this->getPropertyNameInCandidateList($propertyNameLower, $property)) {
                return $foundPropertyName;
            }

            if (null == $propertyName && $this->isIdProperty($propertyNameLower, $entityIdStr)) {
                $this->propertiesFound['id'] = $propertyName = $property->name;
            }
        }

        return $propertyName;
    }

    protected function getPropertyNameInCandidateList($propertyName, $property)
    {
        foreach ($this->candidateProperties as $candidate) {
            if ($propertyName == $candidate) {
                return $this->propertiesFound[$candidate] = $property->name;
            }
        }

        return null;
    }

    protected function isIdProperty($property, $entityIdStr)
    {
        return $property == 'id' || $property == $entityIdStr;
    }

    protected function getName()
    {
        return $this->eventName;
    }

    protected function getReflectionClassFromObject($object)
    {
        $class = get_class($object);

        return new \ReflectionClass($class);
    }

    /**
     * @return \Doctrine\ORM\UnitOfWork
     */
    protected function getUnitOfWork()
    {
        return $this->container->get('doctrine')->getManager()->getUnitOfWork();
    }
}
