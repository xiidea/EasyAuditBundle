<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\ODM;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class UserDocument
{
    /**
     * @ODM\Id(strategy="none")
     */
    private $id;

    /**
     * @ODM\Column(type="string")
     */
    private $username;

    private $roles;

    public function __construct($id = 1, $username = 'admin', $roles = [])
    {
        $this->id = $id;
        $this->username = $username;
        $this->roles = $roles;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->getUsername();
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
