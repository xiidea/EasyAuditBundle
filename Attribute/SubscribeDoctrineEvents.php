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
/* @final */
class SubscribeDoctrineEvents
{
    public array $events = [];

    public function __construct(array|string $values)
    {
        $validValues = [
            'created',
            'updated',
        ];
        $valueValueStr = [
            'created,updated',
            'created, updated',
            'updated,created',
            'updated, created',
        ];
        if (!empty($values) && is_string($values) && !in_array($values, $valueValueStr)) {
            return;
        }
        if (!empty($values) && is_array($values)) {
            foreach ($values as $value) {
                if (!in_array($value, $validValues)) {
                    return;
                }
            }
        }
        $this->events = is_array($values) ? $values : array_map('trim', explode(',', $values));

        $this->events = array_filter($this->events);
    }
}
