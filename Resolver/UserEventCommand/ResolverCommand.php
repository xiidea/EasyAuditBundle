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

abstract class ResolverCommand
{
    /**
     * @param $type
     * @param $template
     * @param $username
     * @return array
     */
    protected function getEventDetailsArray($type, $template, $username)
    {
        return array(
            'type' => $type,
            'description' => sprintf($template, $username)
        );
    }

    /**
     * @param $event
     * @param array $default
     * @return mixed
     */
    abstract public function resolve($event, $default = array());
} 