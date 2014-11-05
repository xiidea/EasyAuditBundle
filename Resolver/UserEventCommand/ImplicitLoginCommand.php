<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Resolver\UserEventCommand;

use FOS\UserBundle\Event\UserEvent;

class ImplicitLoginCommand extends ResolverCommand
{
    /**
     * @param UserEvent $event
     * @param array $default
     * @return mixed
     */
    public function resolve($event, $default = array())
    {
        if( !($event instanceof UserEvent)) {
            return $default;
        }

        return $this->getEventDetailsArray(
            "User Logged in",
            "User '%s' Logged in Successfully using remember me service",
            $event->getUser()->getUsername()
        );
    }
}