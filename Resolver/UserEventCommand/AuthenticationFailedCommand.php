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
     * @return null|array
     */
    public function resolve($event)
    {
        if ($event instanceof AuthenticationFailureEvent) {
            return $this->getEventDetails($event);
        }

        return null;
    }

    /**
     * @param AuthenticationFailureEvent $event
     * @return array
     */
    private function getEventDetails(AuthenticationFailureEvent $event)
    {
        return $this->getEventDetailsArray(
            $event->getAuthenticationToken()->getUser()
        );
    }

    /**
     * @return string
     */
    public function getType()
    {
        return "Authentication Failed";
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return 'Bad credentials Username: %s';
    }
}
