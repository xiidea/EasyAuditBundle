<?php

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Common;


use Symfony\Component\EventDispatcher\Event;
use Xiidea\EasyAuditBundle\Resolver\EventResolverInterface;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog;

class AuditObjectResolver implements EventResolverInterface
{
    public function getEventLogInfo(Event $event)
    {
        $info = new AuditLog();
        $info->setDescription($event->getName());
        $info->setType($event->getName());
        return $info;
    }
}