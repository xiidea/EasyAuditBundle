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
     * @return array
     */
    public function resolve($event)
    {
        if ($event instanceof FilterUserResponseEvent) {
            return $this->getEventDetailsArray($event->getUser()->getUsername());
        }

        return null;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'Password Changed';
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return "Password of user '%s' Changed Successfully";
    }
}
