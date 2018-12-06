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

use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\UserEntity;

class DummyFilterUserResponseEvent extends FilterUserResponseEvent
{
    private $_user;

    public function __construct(UserEntity $user)
    {
        parent::__construct($user, Request::create(''), Response::create());
        $this->_user = $user;
    }

    /**
     * @return UserEntity
     */
    public function getUser()
    {
        return $this->_user;
    }
}
