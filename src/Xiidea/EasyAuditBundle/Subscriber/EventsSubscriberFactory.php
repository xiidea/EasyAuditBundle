<?php

/*
 * This file is part of the EasyAudit package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Subscriber;

use Xiidea\EasyAuditBundle\Traits\ServiceGetterMethods;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventsSubscriberFactory implements EventSubscriberInterface
{
    use ServiceGetterMethods;

    /**
     * @var array
     */
    static private $events = array();
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container,array $events = array())
    {
        $this->container = $container;
        self::$events = array_fill_keys($events, 'resolveEventHandler');
    }

    public function resolveEventHandler($event)
    {
      $eventInfo = $this->getResolver()->getEventInfo($event);
      $this->getLogger()->log($eventInfo);
    }

    public static function getSubscribedEvents()
    {
        return self::$events;
    }
}