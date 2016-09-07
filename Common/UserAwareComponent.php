<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Common;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Xiidea\EasyAuditBundle\Traits\ServiceContainerGetterMethods;

class UserAwareComponent implements ContainerAwareInterface
{
    use ServiceContainerGetterMethods;
    use ContainerAwareTrait;

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Get a user from the Security Context
     *
     * @return mixed
     * @throws \LogicException If SecurityBundle is not available
     */
    public function getUser()
    {
        if (!$this->getContainer()->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->getContainer()->get('security.token_storage')->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    /**
     * @return mixed
     */
    final protected function getImpersonatingUser()
    {
        if (null === $token = $this->getContainer()->get('security.token_storage')->getToken()) {
            return null;
        }

        if ($this->getContainer()->get('security.authorization_checker')->isGranted('ROLE_PREVIOUS_ADMIN')) {
            return $this->getImpersonatingUserFromRole($token);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        $user = $this->getUser();

        if(empty($user)) {
            return $this->getAnonymousUserName();
        }

        return $user->getUsername();
    }

    /**
     * @return string
     */
    protected function getAnonymousUserName()
    {
        $request = $this->getRequest();

        if ($request && $request->getClientIp()) {
            return "Anonymous";
        }

        return "By Command";
    }

    /**
     * @param $token
     * @param null $user
     * @return mixed
     */
    protected function getImpersonatingUserFromRole($token, $user = null)
    {
        foreach ($token->getRoles() as $role) {
            if ($role instanceof SwitchUserRole) {
                $user = $role->getSource()->getUser();
                break;
            }
        }

        return $user;
    }
}
