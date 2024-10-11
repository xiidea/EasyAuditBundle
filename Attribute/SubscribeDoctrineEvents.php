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
/* @final */ class SubscribeDoctrineEvents
{
    public $events = array();

    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $values['events'] = $values['value'];
        }
        if (!isset($values['events'])) {
            return;
        }

        $this->events = is_array($values['events']) ? $values['events'] : array_map('trim', explode(',', $values['events']));

        $this->events = array_filter($this->events);
    }
}
