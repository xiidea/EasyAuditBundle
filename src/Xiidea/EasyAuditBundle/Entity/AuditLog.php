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

abstract class AuditLog
{
    use EntityHydrationMethod;

    /**
     * @var integer
     */
    protected $id;

    /**
     * Type Of Event
     *
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $description;

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \DateTime
     */
    public function getEventTime()
    {
        return $this->eventTime;
    }

    /**
     * @param \DateTime $eventTime
     */
    public function setEventTime(\DateTime $eventTime)
    {
        $this->eventTime = $eventTime;
    }
}