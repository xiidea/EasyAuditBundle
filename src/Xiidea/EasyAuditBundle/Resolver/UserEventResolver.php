<?php

/*
 * This file is part of the EasyAudit package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Resolver;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\UserEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/** Custom Event Resolver Example Class */
class UserEventResolver implements EventResolverInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container){

        $this->container = $container;
    }

    /**
     * @param $event
     *
     * @return array
     */
    public function getEventLogInfo($event = null)
    {
        $eventDetails = $this->getEventLogDetails($event);

        return array(
            'description'=>$eventDetails['description'],
            'type'=>$eventDetails['type']
        );
    }

    private function getEventLogDetails($event)
    {
        $name = $event->getName();

        $eventDetails = array(
            'type' => $name,
            'description'=>$name
        );

        switch($name){
            case 'fos_user.change_password.edit.success':
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
        }

        return $eventDetails;
    }

    /**
     * Get a user from the Security Context
     *
     * @return mixed
     * @throws \LogicException If SecurityBundle is not available
     */
    public function getUser()
    {
        if (!$this->container->has('security.context')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->container->get('security.context')->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    public function getUsername()
    {
        $user = $this->getUser();

        if($user == null){
            return 'Anonymous';
        }

        return $user->getUsername();
    }
}
