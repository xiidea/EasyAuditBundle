<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Annotation;


/**
 * Annotation for ORM Subscribed Event.
 *
 * @Annotation
 * @Target({"CLASS"})
 *
 * @author Roni Saha <roni@xiidea.net>
 */
final class ORMSubscribedEvents
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
