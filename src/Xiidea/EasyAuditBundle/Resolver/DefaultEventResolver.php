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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Xiidea\EasyAuditBundle\Event\LogEvent;

class DefaultEventResolver implements EventResolverInterface
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
     * @return LogEventInterface
     */
    public function getEventLogInfo($event = null)
    {
        $logEvent = new LogEvent();
        $logEvent->setDescription($event->getname());
        $logEvent->setType($event->getname());
        $logEvent->setUser($this->getUsername());

        return $logEvent;
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

        return $user->getFullName();
    }

}