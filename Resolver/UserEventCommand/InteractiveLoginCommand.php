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

class InteractiveLoginCommand extends ResolverCommand
{
    /**
     * @var UserAwareComponent
     */
    private $userAwareComponent;

    public function __construct(UserAwareComponent $userAwareComponent)
    {
        $this->userAwareComponent = $userAwareComponent;
    }

    /**
     * @param $event
     * @param array $default
     * @return mixed
     */
    public function resolve($event, $default = array())
    {
        return $this->getEventDetailsArray(
            "User Logged in",
            "User '%s' Logged in Successfully",
            $this->userAwareComponent->getUsername()
        );
    }
}