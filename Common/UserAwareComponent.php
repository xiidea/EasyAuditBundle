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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserAwareComponent
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authChecker;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function setTokenStorage($tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Get a user from the Security Context.
     *
     * @return mixed
     *
     * @throws \LogicException If SecurityBundle is not available
     */
    public function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    /**
     * @param AuthorizationCheckerInterface $authChecker
     */
    public function setAuthChecker($authChecker)
    {
        $this->authChecker = $authChecker;
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack($requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return mixed
     */
    final protected function getImpersonatingUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if ($this->authChecker->isGranted('IS_IMPERSONATOR')) {
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

        if (empty($user)) {
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
            return 'Anonymous';
        }

        return 'By Command';
    }

    /**
     * @param TokenInterface $token
     * @param null           $user
     *
     * @return mixed
     */
    protected function getImpersonatingUserFromRole($token, $user = null)
    {
        if ($token instanceof SwitchUserToken) {
            $user = $token->getOriginalToken()->getUser();
        }

        return $user;
    }

    protected function getRequest()
    {
        if (null === $this->requestStack) {
            return false;
        }

        return $this->requestStack->getCurrentRequest();
    }
}
