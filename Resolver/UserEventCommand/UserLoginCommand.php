<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Resolver\UserEventCommand;


abstract class UserLoginCommand extends ResolverCommand
{

    /**
     * @return string
     */
    public function getType()
    {
        return "User Logged in";
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return "User '%s' Logged in Successfully";
    }
}
