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
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Xiidea\EasyAuditBundle\Events\DoctrineEntityEvent;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;

/** Custom Event Resolver Example Class */
class EntityEventResolver implements ContainerAwareInterface, EventResolverInterface
{
    use ContainerAwareTrait;

    protected $candidateProperties = array('name', 'title');

    protected $propertiesFound = array();

    protected $eventShortName;

    /** @var  $event DoctrineEntityEvent */
    protected $event;

    protected $entity;

    protected $eventName;


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

        $entityClass = $this->getReflectionClassFromObject($this->entity);

        return array(
            'description' => $this->getDescription($entityClass),
            'type' => $this->getEventType($entityClass->getShortName()),
        );

    }

    /**
     * @param DoctrineEntityEvent $event
     * @param string $eventName
     */
    private function initialize(DoctrineEntityEvent $event, $eventName)
    {
        $this->eventShortName = null;
        $this->propertiesFound = array();
        $this->eventName = $eventName;
        $this->event = $event;
        $this->entity = $event->getLifecycleEventArgs()->getEntity();
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
     * @param string $name
     * @return string|mixed
     */
    protected function getProperty($name)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        try {
            return $propertyAccessor->getValue($this->entity, $this->propertiesFound[$name]);
        } catch (NoSuchPropertyException $e) {
            return '{INACCESSIBLE} property! ' . $e->getMessage();
        }
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
     * @param \ReflectionClass $reflectionClass
     * @return string
     */
    protected function getDescription(\ReflectionClass $reflectionClass)
    {
        $property = $this->getBestCandidatePropertyForIdentify($reflectionClass);

        $descriptionTemplate = '%s has been %s';

        if (!empty($property)) {
            $descriptionTemplate .= sprintf(' with %s = "%s"', $property, $this->getProperty($property));
        }

        return sprintf(
            $descriptionTemplate,
            $reflectionClass->getShortName(),
            $this->getEventShortName()
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
     * @param \ReflectionClass $reflectionClass
     * @return null|string
     */
    protected function getBestCandidatePropertyForIdentify(\ReflectionClass $reflectionClass)
    {
        $foundPropertyName = $this->getPropertyNameInCandidateList($reflectionClass);

        if ("" !== $foundPropertyName) {
            return $foundPropertyName;
        }

        return $this->getNameOrIdPropertyFromPropertyList($reflectionClass,
            strtolower($reflectionClass->getShortName()) . "id"
        );
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return string
     */
    protected function getPropertyNameInCandidateList(\ReflectionClass $reflectionClass)
    {
        foreach ($this->candidateProperties as $property) {
            if($reflectionClass->hasProperty($property)) {
                return $this->propertiesFound[$property] = $property;
            }
        }

        return "";
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

    /**
     * @param \ReflectionClass $reflectionClass
     * @param $entityIdStr
     * @return null|string
     */
    private function getNameOrIdPropertyFromPropertyList(\ReflectionClass $reflectionClass, $entityIdStr)
    {
        foreach (array('id', $entityIdStr) as $field) {
            if($reflectionClass->hasProperty($field)) {
                return $this->propertiesFound['id'] = $field;
            }
        }

        return "";
    }
}
