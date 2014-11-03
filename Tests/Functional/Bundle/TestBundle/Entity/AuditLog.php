<?php
/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Functional\Bundle\TestBundle\Entity;

use Xiidea\EasyAuditBundle\Entity\BaseAuditLog;


class AuditLog extends BaseAuditLog
{
    public function __toString() {
        return md5($this->getTypeId() . $this->getType() . $this->getIp());
    }
}