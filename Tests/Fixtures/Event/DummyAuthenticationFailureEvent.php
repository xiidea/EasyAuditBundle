<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Event;


use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\DummyToken;

class DummyAuthenticationFailureEvent extends AuthenticationFailureEvent
{
    private $authenticationToken;

    public function __construct($user = "user")
    {
        $this->setName('security.authentication.failure');
        $this->authenticationToken = new DummyToken($user);
    }

    public function getAuthenticationToken()
    {
        return $this->authenticationToken;
    }
}