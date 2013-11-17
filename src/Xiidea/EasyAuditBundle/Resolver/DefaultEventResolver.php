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

use Symfony\Component\DependencyInjection\ContainerInterface;

class DefaultEventResolver implements EventResolverInterface
{

    /**
     * @param $event
     *
     * @return LogEventInterface
     */
    public function getEventLogInfo($event = null)
    {
        return array(
            'description'=>$event->getname(),
            'type'=>$event->getname(),
        );
    }
}