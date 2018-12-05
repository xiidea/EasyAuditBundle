<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Event;


class DoctrineEvent extends Basic {

    public function __construct($type = "created") {
        parent::__construct('easy_audit.doctrine.object.' . $type);
    }
}