<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Events;

class DoctrineEvents
{
    private static $prefix = 'easy_audit.doctrine.object.';

    const ENTITY_UPDATED = 'easy_audit.doctrine.object.updated';
    const ENTITY_CREATED = 'easy_audit.doctrine.object.created';
    const ENTITY_DELETED = 'easy_audit.doctrine.object.deleted';

    /**
     * @param string $eventName
     *
     * @return string
     */
    public static function getShortEventType($eventName)
    {
        return str_replace(self::$prefix, '', $eventName);
    }

    /**
     * @return array
     *
     * @throws \ReflectionException
     */
    public static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}
