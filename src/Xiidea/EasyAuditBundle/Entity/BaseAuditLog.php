<?php

/*
 * This file is part of the EasyAudit package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Entity;

use Xiidea\EasyAuditBundle\Traits\EntityHydrationMethod;

abstract class BaseAuditLog
{
    use EntityHydrationMethod;

    /**
     * Time Of Event
     * @var \DateTime
     */
    protected $eventTime;
    protected $user;

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return \DateTime
     */
    final public function getEventTime()
    {
        return $this->eventTime;
    }

    /**
     * @param \DateTime $eventTime
     */
    final public function setEventTime(\DateTime $eventTime)
    {
        $this->eventTime = $eventTime;
    }
}