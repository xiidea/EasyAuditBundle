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
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Xiidea\EasyAuditBundle\Common\UserAwareComponent;

/** Custom Event Resolver Example For FosUserBundle  */
class UserEventResolver extends UserAwareComponent implements EventResolverInterface
{

    /**
     * @param Event $event
     *
     * @return array
     */
    public function getEventLogInfo(Event $event)
    {
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
                $eventDetails = $this->handlePasswordChangedEvent($event);
                break;

            case 'security.interactive_login':
                $eventDetails = $this->handleLoginEventBy($this->getUsername());
                break;

            case 'fos_user.security.implicit_login':
                /** @var $event UserEvent */
                $eventDetails = $this->handleLoginEventBy($event->getUser()->getUsername(), "remember me service");
                break;

            case 'security.authentication.failure':
                /** @var AuthenticationFailureEvent $event */
                $eventDetails = $this->handleAuthenticationFailureEvent($event);
                break;
        }

        return $eventDetails;
    }

    private function getEventDetailsArray($type, $template, $username)
    {
        return array(
            'type' => $type,
            'description' => sprintf($template, $username)
        );
    }

    /**
     * @param FilterUserResponseEvent $event
     * @return array
     */
    protected function handlePasswordChangedEvent(FilterUserResponseEvent $event)
    {
        $eventDetails = $this->getEventDetailsArray(
            "Password Changed",
            "Password of user '%s' Changed Successfully",
            $event->getUser()->getUsername()
        );
        
        return $eventDetails;
    }

    private function handleLoginEventBy($getUsername, $using = "")
    {
        return $this->getEventDetailsArray(
            "User Logged in",
            "User '%s' Logged in Successfully" . ("" == $using ? "" : " using $using"),
            $getUsername
        );
    }

    private function handleAuthenticationFailureEvent(AuthenticationFailureEvent $event)
    {
        return $this->getEventDetailsArray(
            "Authentication Failed",
            'Bad credentials Username: %s',
            $event->getAuthenticationToken()->getUser()
        );
    }
}
