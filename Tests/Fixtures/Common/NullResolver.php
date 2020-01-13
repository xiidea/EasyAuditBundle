<?php

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Common;

use Symfony\Contracts\EventDispatcher\Event;
use Xiidea\EasyAuditBundle\Resolver\EventResolverInterface;

class NullResolver implements EventResolverInterface
{
    public function getEventLogInfo(Event $event, $eventName)
    {
        return null;
    }
}
