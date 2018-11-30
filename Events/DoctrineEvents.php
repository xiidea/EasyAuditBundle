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
    private static $prefix = 'easy_audit.doctrine.document.';

    const DOCUMENT_UPDATED = 'easy_audit.doctrine.document.updated';
    const DOCUMENT_CREATED = 'easy_audit.doctrine.document.created';
    const DOCUMENT_DELETED = 'easy_audit.doctrine.document.deleted';

    /**
     * @param string $eventName
     * @return string
     */
    public static function getShortEventType($eventName)
    {
        return str_replace(self::$prefix, '', $eventName);
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
