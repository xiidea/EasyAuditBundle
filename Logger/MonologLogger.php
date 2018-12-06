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
use Xiidea\EasyAuditBundle\Model\BaseAuditLog as AuditLog;

class MonologLogger implements LoggerInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    private static $ignoreProperties = array('description', 'id', 'level');

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function log(AuditLog $event = null)
    {
        if (null === $event) {
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
        return $refObject->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_STATIC);
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

        $arr = array();

        foreach ($this->getAllProperties($refObject) as $property) {
            if (!$accessor->isReadable($event, $property->getName()) || in_array($property->getName(), self::$ignoreProperties)) {
                continue;
            }

            $arr[$property->getName()] = $accessor->getValue($event, $property->getName());
        }

        return $arr;
    }
}
