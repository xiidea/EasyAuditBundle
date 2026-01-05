<?php

namespace Xiidea\EasyAuditBundle\Common;

use Doctrine\Persistence\Proxy;

class ClassUtils
{
    /**
     * Gets the real class name of a class name that could be a proxy.
     *
     * @param string $className
     *
     * @return string
     * @psalm-return class-string
     */
    public static function getRealClass(string $className): string
    {
        $pos = strrpos($className, '\\' . Proxy::MARKER . '\\');

        if ($pos === false) {
            return $className;
        }

        return substr($className, $pos + Proxy::MARKER_LENGTH + 2);
    }

    /**
     * Gets the real class name of an object (even if its a proxy).
     *
     * @param object $object
     *
     * @return string
     * @psalm-return class-string
     */
    public static function getClass(object $object): string
    {
        return self::getRealClass(get_class($object));
    }
}
