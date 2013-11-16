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

use Xiidea\EasyAuditBundle\Traits\ServiceContainerGetterMethods;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class EventsSubscriber implements EventSubscriberInterface
{
    public function __construct(ContainerInterface $container, $events = array())
    {
        $container->get('event_dispatcher')->addSubscriber(new EventsSubscriberFactory(
            $container,
            $events
        ));

    }

    public function onKernelRequest(){

    }

    public static function getSubscribedEvents()
    {
        return array(
            'kernel.request' => 'onKernelRequest'
        );
    }
}