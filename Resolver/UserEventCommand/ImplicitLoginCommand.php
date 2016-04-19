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

class ImplicitLoginCommand extends UserLoginCommand
{
    /**
     * @param UserEvent $event
     * @return mixed
     */
    public function resolve($event)
    {
        if ($event instanceof UserEvent) {
            return $this->getEventDetailsArray($event->getUser()->getUsername());
        }

        return null;
    }

    public function getTemplate()
    {
        return parent::getTemplate()." using remember me service";
    }
}
