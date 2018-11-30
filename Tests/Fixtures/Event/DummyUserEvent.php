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


use FOS\UserBundle\Event\UserEvent;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ODM\UserDocument;

class DummyUserEvent extends UserEvent
{
    private $_user;

    public function __construct(UserDocument $user)
    {
        $this->_user = $user;
    }

    /**
     * @return UserDocument
     */
    public function getUser()
    {
        return $this->_user;
    }

}