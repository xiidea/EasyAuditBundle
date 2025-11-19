<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Attribute;

/**
 * Attribute for ORM Subscribed Event.
 *
 * @author Roni Saha <roni@xiidea.net>
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class SubscribeDoctrineEvents
{
    public array $events = [];

    public function __construct(array|string $values)
    {
        $this->events = is_array($values) ? $values : array_map('trim', explode(',', $values));

        $this->events = array_filter($this->events);
    }
}
