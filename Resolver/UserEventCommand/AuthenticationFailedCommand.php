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


use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

class AuthenticationFailedCommand extends ResolverCommand
{
    /**
     * @param AuthenticationFailureEvent $event
     * @param array $default
     * @return array
     */
    public function resolve($event, $default = array())
    {
        if( !($event instanceof AuthenticationFailureEvent)) {
            return $default;
        }

        return $this->getEventDetailsArray(
            "Authentication Failed",
            'Bad credentials Username: %s',
            $event->getAuthenticationToken()->getUser()
        );
    }
}