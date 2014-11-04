<?php

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Common;


use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xiidea\EasyAuditBundle\Resolver\EventResolverInterface;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog;

class AuditObjectResolver implements EventResolverInterface
{
    public function getEventLogInfo(Event $event, $eventName)
    {
        $info = new AuditLog();
        $info->setDescription($eventName);
        $info->setType($eventName);

        return $info;
    }
}