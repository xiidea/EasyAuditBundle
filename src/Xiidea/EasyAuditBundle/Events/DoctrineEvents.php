<?php

/*
 * This file is part of the EasyAudit package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Events;

class DoctrineEvents
{
    private static $prefix = 'easy_audit.doctrine.entity.';

    const ENTITY_UPDATED = 'easy_audit.doctrine.entity.updated';
    const ENTITY_CREATED = 'easy_audit.doctrine.entity.created';

    public static function getShortEventType($eventName)
    {
        return str_replace(self::$prefix, '', $eventName);
    }
}