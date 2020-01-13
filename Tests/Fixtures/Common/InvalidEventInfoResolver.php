<?php

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Common;

use Symfony\Contracts\EventDispatcher\Event;
use Xiidea\EasyAuditBundle\Resolver\EventResolverInterface;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\UserEntity;

class InvalidEventInfoResolver implements EventResolverInterface
{
    public function getEventLogInfo(Event $event, $eventName)
    {
        return new UserEntity();
    }
}
