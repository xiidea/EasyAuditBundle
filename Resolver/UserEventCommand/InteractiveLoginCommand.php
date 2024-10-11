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

use Xiidea\EasyAuditBundle\Common\UserAwareComponent;

class InteractiveLoginCommand extends UserLoginCommand
{
    public function __construct(private UserAwareComponent $userAwareComponent)
    {
    }

    /**
     * @param $event
     *
     * @return mixed
     */
    #[\Override]
    public function resolve($event)
    {
        return $this->getEventDetailsArray($this->userAwareComponent->getUsername());
    }
}
