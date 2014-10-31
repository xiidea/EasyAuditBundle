<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Resolver;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\Event;
use Xiidea\EasyAuditBundle\Common\UserAwareComponent;

/** Custom Event Resolver Example For FosUserBundle  */
class UserEventResolver extends UserAwareComponent implements EventResolverInterface
{

    /**
     * @param $event
     *
     * @return array
     */
    public function getEventLogInfo(Event $event = null)
    {
        if (null === $event) {
            return null;
        }

        $eventDetails = $this->getEventLogDetails($event);

        return array(
            'description' => $eventDetails['description'],
            'type' => $eventDetails['type']
        );
    }

    protected function getEventLogDetails(Event $event)
    {
        $name = $event->getName();

        $eventDetails = array(
            'type' => $name,
            'description' => $name
        );

        switch ($name) {
            case 'fos_user.change_password.edit.completed':
                /** @var $event FilterUserResponseEvent */
                $eventDetails['type'] = "Password Changed";
                $eventDetails['description'] = sprintf(
                    "Password of user '%s' Changed Successfully",
                    $event->getUser()->getUsername()
                );

                break;
            case 'security.interactive_login':
                $eventDetails['type'] = "User Logged in";
                $eventDetails['description'] = sprintf(
                    "User '%s' Logged in Successfully",
                    $this->getUsername()
                );
                break;
            case 'fos_user.security.implicit_login':
                /** @var $event UserEvent */
                $eventDetails['type'] = "User Logged in";
                $eventDetails['description'] = sprintf(
                    "User '%s' Logged in Successfully by remember me service",
                    $event->getUser()->getUsername()
                );
                break;
            case 'security.authentication.failure':
                /** @var $event AuthenticationFailureEvent */
                $eventDetails['type'] = "Authentication Failed";
                $translator = $this->container->get('translator');
                $eventDetails['description'] = $translator->trans($event->getAuthenticationException()->getMessage());
                $eventDetails['description'] .= " Username: " . $event->getAuthenticationToken()->getUser();
                break;
        }

        return $eventDetails;
    }
}
