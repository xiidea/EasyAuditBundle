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

use Symfony\Component\DependencyInjection\ContainerAware;

class UserAwareComponent extends ContainerAware
{
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

        if($user === null){
            return isset($_SERVER['HTTP_HOST']) ? 'Anonymous' : 'By Command';
        }

        return $user->getUsername();
    }
}