<?php

namespace Xiidea\EasyAuditBundle\Common;

use Doctrine\Persistence\Proxy;

class ClassUtils
{
    public static function getClass($entity): string
    {
        if ($entity instanceof Proxy) {
            return get_parent_class($entity);
        }

        return get_class($entity);
    }
}