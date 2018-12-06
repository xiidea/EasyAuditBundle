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
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\DummyToken;

class DummyAuthenticationFailureEvent extends AuthenticationFailureEvent
{
    public function __construct($user = 'user')
    {
        parent::__construct(new DummyToken($user), new AuthenticationException());
    }
}
