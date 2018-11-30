<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Event;

use Xiidea\EasyAuditBundle\Resolver\EmbeddedEventResolverInterface;

class WithEmbeddedResolver extends Basic implements EmbeddedEventResolverInterface
{
    public function getEventLogInfo($eventName)
    {
        return [
            'description' => 'It is an embedded event',
            'type'        => $eventName,
            'level'       => 'emergency'
        ];
    }
}