<?php

/*
 * This file is part of the EasyAudit package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Event;

interface LogEventInterface
{
    public function getType();
    public function getEventTime();
    public function getDescription();
    public function getUser();
}