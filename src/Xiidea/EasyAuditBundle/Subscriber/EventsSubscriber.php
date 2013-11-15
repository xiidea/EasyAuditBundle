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
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class EventsSubscriber implements EventSubscriberInterface
{
    use ServiceGetterMethods;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;
    private static $events = array();

    public function __construct(ContainerInterface $container, $events = array())
    {
        $this->container = $container;

        self::$events = $events;

    }

    public function onKernelResponse(GetResponseEvent $event)
    {
        if (empty(self::$events)) {
            return;
        }

        $event->getDispatcher()->addSubscriber(new EventsSubscriberFactory(
            $this->container,
            $this->getEventsForLazySubscribing()
        ));
    }

    public function filterLogEvents($event)
    {
        if (in_array($event->getName(), self::$events)) {
            $eventInfo = $this->getResolver()->getEventInfo($event);
            $this->getLogger()->log($eventInfo);
        }
    }

    /**
     * Return Events List Excluding Events handled by this class
     *
     * @return array
     */
    protected function getEventsForLazySubscribing()
    {
        $events = array();
        $subscribedEvents = self::getSubscribedEvents();

        foreach (self::$events as $event) {
            if (isset($subscribedEvents[$event])) {
                continue;
            }

            $events[] = $event;
        }

        return $events;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'kernel.request'                   => array('onKernelResponse', 0),
            'security.interactive_login'       => 'filterLogEvents',
            'fos_user.security.implicit_login' => 'filterLogEvents'
        );
    }
}