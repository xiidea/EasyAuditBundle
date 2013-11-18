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

use Xiidea\EasyAuditBundle\Event\EventResolverInterface;
use Xiidea\EasyAuditBundle\Resolver\EventResolverFactory;
use Xiidea\EasyAuditBundle\Traits\ServiceContainerGetterMethods;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventsSubscriberFactory implements EventSubscriberInterface
{
    use ServiceContainerGetterMethods;

    /**
     * @var array
     */
    static private $events = array();

    public function __construct(ContainerInterface $container,array $events = array())
    {
        $this->container = $container;
        $this->addDoctrineEvents($events);

        self::$events = array_fill_keys($events, 'resolveEventHandler');
    }

    private function addDoctrineEvents(&$events = array())
    {
        $doctrine_entities = $this->getParameter('doctrine_entities');

        if (empty($doctrine_entities)) {
            return;
        }

        $reflectionClass = new \ReflectionClass('Xiidea\EasyAuditBundle\Events\DoctrineEvents');
        $constants = $reflectionClass->getConstants();

        foreach ($constants as $constant) {
            array_push($events, $constant);
        }
    }

    public function resolveEventHandler($event)
    {
        $eventResolverFactory = new EventResolverFactory($this->container);
        $eventInfo = $eventResolverFactory->getEventLog($event);
        $this->getLogger()->log($eventInfo);
    }

    protected function getEventLogObject($event)
    {
        $logEventClass = $this->getParameter('log_event_class');
        $eventLog = $this->getEventLogInfo($event);
    }

    protected function eventWithResolver($event)
    {
        return ($event instanceof EventResolverInterface) || method_exists($event,'getEventLogInfo');
    }

    protected function getEventLogInfo($event)
    {
        if ($this->eventWithResolver($event)) {
            return $event->getEventLogInfo();
        }

        return $this->getCommonResolver()->getEventLogInfo($event);
    }

    public static function getSubscribedEvents()
    {
        return self::$events;
    }
}