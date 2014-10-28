<?php

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Common;


use Xiidea\EasyAuditBundle\Resolver\EventResolverInterface;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\UserEntity;

class InvalidEventInfoResolver implements EventResolverInterface
{
    public function getEventLogInfo()
    {
        return new UserEntity();
    }
}