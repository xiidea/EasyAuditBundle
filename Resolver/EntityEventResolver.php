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

        $entity = $this->getEntity();

        $changesMetaData = $this->getChangeSets($entity);

        if ($this->isUpdateEvent() && $changesMetaData === null) {
            return null;
        }

        $reflectionClass = $this->getReflectionClassFromObject($entity);

        $typeName = $reflectionClass->getShortName();
        $eventType = $this->getEventType($typeName);
        $eventDescription = $this->getDescription($reflectionClass, $typeName);

        return array(
            'description' => $eventDescription,
            'type' => $eventType,
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
        $entity = $this->getEntity();
        $propertyGetter = 'get' . $this->propertiesFound[$name];

        return $entity->$propertyGetter();
    }

    protected function getEventType($typeName)
    {
        return $typeName . " " . $this->getEventShortName();
    }

    protected function getDescription(\ReflectionClass $reflectionClass, $typeName)
    {
        $property = $this->getBestCandidatePropertyForIdentify($reflectionClass);

        $descriptionTemplate = '%s has been %s';

        if ($property) {
            $descriptionTemplate .= sprintf(' with %s = "%s"', $property, $this->getProperty($property));
        }

        return sprintf(
            $descriptionTemplate,
            $typeName,
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

        $hasIdProperty = false;
        $idPropertyName = null;

        foreach ($properties as $property) {
            $propertyName = strtolower($property->name);
            $hasIdProperty = $hasIdProperty || $this->isId($propertyName, $reflectionClass);

            if (!$idPropertyName && $hasIdProperty) {
                $idPropertyName = $property->name;
                $this->propertiesFound['id'] = $idPropertyName;
            }

            foreach ($this->candidateProperties as $candidate) {
                $hasNameProperty = $this->isPropertyName($propertyName, $candidate);

                if ($hasNameProperty) {
                    $this->propertiesFound[$candidate] = $property->name;

                    return $property->name;
                }
            }
        }

        return $idPropertyName;
    }

    protected function isPropertyName($property, $name)
    {
        return $property == $name;
    }

    protected function isId($property, \ReflectionClass $reflectionClass)
    {
        $entityIdStr = strtolower($reflectionClass->getShortName()) . "id";

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
