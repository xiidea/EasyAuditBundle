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


use FOS\UserBundle\Event\FilterUserResponseEvent;

class PasswordChangedCommand extends ResolverCommand
{

    /**
     * @param FilterUserResponseEvent $event
     * @param array $default
     * @return array
     */
    public function resolve($event, $default = array())
    {
        if( !($event instanceof FilterUserResponseEvent)) {
            return $default;
        }

        return $this->getEventDetailsArray(
            "Password Changed",
            "Password of user '%s' Changed Successfully",
            $event->getUser()->getUsername()
        );
    }
}