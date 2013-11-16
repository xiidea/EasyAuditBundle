<?php

/*
 * This file is part of the EasyAudit package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Resolver;

/** Custom Event Resolver Example Class */
class CustomEventResolver implements EventResolverInterface
{
    /**
     * @param $event
     *
     * @return array
     */
    public function getEventLogInfo($event = null)
    {
        return array(
            'description'=>'Custom description',
            'type'=>$event->getname(),
            'user'=>'Anonymous',
        );
    }
}