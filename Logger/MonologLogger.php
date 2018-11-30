<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Logger;

use ReflectionProperty;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Xiidea\EasyAuditBundle\Document\BaseAuditLog as AuditLog;

class MonologLogger implements LoggerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private static $ignoreProperties = ['description', 'id', 'level'];

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function log(AuditLog $event = null)
    {
        if ($event === null) {
            return;
        }

        $this->logger->log($event->getLevel(), $event->getDescription(), $this->getContextArray($event));
    }

    /**
     * @param \ReflectionObject $refObject
     *
     * @return ReflectionProperty[]
     */
    protected function getAllProperties(\ReflectionObject $refObject)
    {
        return $refObject->getProperties(
            ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_STATIC
        );
    }

    /**
     * @param AuditLog $event
     *
     * @return array
     */
    protected function getContextArray(AuditLog $event)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $refObject = new \ReflectionObject($event);

        $arr = [];

        foreach ($this->getAllProperties($refObject) as $property) {
            $propertyName = $property->getName();
            if (!$accessor->isReadable($event, $propertyName) || \in_array(
                    $propertyName,
                    self::$ignoreProperties,
                    true
                )) {
                continue;
            }

            $arr[$property->getName()] = $accessor->getValue($event, $propertyName);
        }

        return $arr;
    }
}
