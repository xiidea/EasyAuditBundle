<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Traits;

use Symfony\Component\PropertyAccess\PropertyAccess;

trait DocumentHydrationMethod
{
    final public function fromArray($data = array())
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($data as $property => $value) {
            if($accessor->isWritable($this, $property)) {
                $accessor->setValue($this, $property, $value);
            }
        }

        return $this;
    }
}
