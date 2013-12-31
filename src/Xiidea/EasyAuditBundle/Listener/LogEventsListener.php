<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Xiidea\EasyAuditBundle\Logger\LoggerFactory;
use Xiidea\EasyAuditBundle\Resolver\EventResolverFactory;
use Xiidea\EasyAuditBundle\Resolver\EventResolverInterface;
use Xiidea\EasyAuditBundle\Traits\ServiceContainerGetterMethods;

class LogEventsListener extends ContainerAware
{
    use ServiceContainerGetterMethods;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->loggerFactory = $loggerFactory;
    }

    public function resolveEventHandler($event)
    {
        $eventResolverFactory = new EventResolverFactory();
        $eventResolverFactory->setContainer($this->container);
        $eventInfo = $eventResolverFactory->getEventLog($event);
        $this->loggerFactory->executeLoggers($eventInfo);
    }

    protected function getEventLogInfo($event)
    {
        if ($event instanceof EventResolverInterface) {
            return $event->getEventLogInfo();
        }

        return $this->getCommonResolver()->getEventLogInfo($event);
    }

}