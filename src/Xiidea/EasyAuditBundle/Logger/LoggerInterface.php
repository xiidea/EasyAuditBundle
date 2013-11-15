<?php

/*
 * This file is part of the EasyAudit package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Logger;

use Xiidea\EasyAuditBundle\Event\LogEventInterface;

interface LoggerInterface
{
    public function log(LogEventInterface $event);
} 