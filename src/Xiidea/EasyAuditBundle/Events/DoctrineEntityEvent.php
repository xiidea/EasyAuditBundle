<?php
/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Events;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Xiidea\EasyAuditBundle\Resolver\EventResolverInterface;

class DoctrineEntityEvent extends Event implements EventResolverInterface
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;
    /**
     * @var \Doctrine\ORM\Event\LifecycleEventArgs
     */
    private $lifecycleEventArgs;

    private $eventShortName;

    public function __construct(ContainerInterface $container, LifecycleEventArgs $lifecycleEventArgs)
    {

        $this->container = $container;
        $this->lifecycleEventArgs = $lifecycleEventArgs;
    }

    /**
     * @return LifecycleEventArgs
     */
    public function getLifecycleEventArgs()
    {
        return $this->lifecycleEventArgs;
    }

    public function getEventLogInfo($event = NULL)
    {
        $entity = $this->getLifecycleEventArgs()->getEntity();
        $reflectionClass = $this->getReflectionClassFromObject($entity);

        $eventType = $this->getEventType($reflectionClass);
        $eventDescription = $this->getDescription($reflectionClass, $entity);

        return array(
            'description'=> $eventDescription,
            'type'=> $eventType,
        );

    }

    private function getEventType(\ReflectionClass $reflectionClass)
    {
        return $reflectionClass->getShortName() . " " . $this->getEventShortName();
    }

    private function getDescription(\ReflectionClass $reflectionClass, $entity)
    {
        $property = $this->getBestCandidatePropertyForIdentify($reflectionClass);

        $descriptionTemplate = '%s has been %s ';

        if($property){
            $propertyGetter = 'get'. $property;

            $descriptionTemplate .= sprintf(' with %s = "%s" ', $property,  $entity->$propertyGetter());
        }

        return sprintf($descriptionTemplate,
                       $reflectionClass->getShortName(),
                       $this->getEventShortName());
    }

    private function getEventShortName(){

        if(!$this->eventShortName){
            $this->eventShortName = DoctrineEvents::getShortEventType($this->getName());
        }

        return $this->eventShortName;
    }

    private function getBestCandidatePropertyForIdentify(\ReflectionClass  $reflectionClass)
    {
        $properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PROTECTED);

        $hasNameProperty = false;
        $hasTitleProperty = false;
        $hasIdProperty = false;

        $idPropertyName = null;

        foreach($properties as $property)
        {
            $propertyName = strtolower($property->name);
            $hasIdProperty = $hasIdProperty || $this->isId($propertyName, $reflectionClass);

            if($hasIdProperty){
                $idPropertyName = $property->name;
            }

            $hasNameProperty = $hasNameProperty || $this->isPropertyName($propertyName, 'name');

            if($hasNameProperty){
                return $property->name;
            }

            $hasTitleProperty = $hasTitleProperty || $this->isPropertyName($propertyName, 'title');

            if($hasTitleProperty){
                return $property->name;
            }
        }

        return $idPropertyName;
    }

    private function isPropertyName($property, $name)
    {
       return $property == $name;
    }

    private function isId($property, \ReflectionClass  $reflectionClass)
    {
        $entityIdStr = strtolower($reflectionClass->getShortName()) . "id";

        return $property == 'id' || $property == $entityIdStr;
    }

    private function getReflectionClassFromObject($object)
    {
        $class = get_class($object);

        return new \ReflectionClass($class);
    }

}