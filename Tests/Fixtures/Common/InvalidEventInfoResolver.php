<?php

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Common;

use Symfony\Component\EventDispatcher\Event;
use Xiidea\EasyAuditBundle\Resolver\EventResolverInterface;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ODM\UserDocument;

class InvalidEventInfoResolver implements EventResolverInterface
{
    public function getEventLogInfo(Event $event, $eventName)
    {
        return new UserDocument();
    }
}