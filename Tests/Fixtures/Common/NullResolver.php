<?php

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Common;

use Symfony\Component\EventDispatcher\Event;
use Xiidea\EasyAuditBundle\Resolver\EventResolverInterface;

class NullResolver implements EventResolverInterface
{
    public function getEventLogInfo(Event $event, $eventName)
    {
        return null;
    }
}