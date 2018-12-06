<?php

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Common;

class InvalidLogger
{
    public function log($event)
    {
        return $event;
    }
}
