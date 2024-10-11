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

use Symfony\Contracts\EventDispatcher\Event;
use Xiidea\EasyAuditBundle\Logger\LoggerFactory;
use Xiidea\EasyAuditBundle\Resolver\EventResolverFactory;

class LogEventsListener
{
    public function __construct(private LoggerFactory $loggerFactory, private EventResolverFactory $resolverFactory)
    {
    }

    public function resolveEventHandler(Event $event, $eventName)
    {
        $eventInfo = $this->resolverFactory->getEventLog($event, $eventName);
        $this->loggerFactory->executeLoggers($eventInfo);
    }
}
