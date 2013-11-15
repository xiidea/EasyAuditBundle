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

use Xiidea\EasyAuditBundle\Traits\EntityHydrationMethod;

class LogEvent implements LogEventInterface
{
    use EntityHydrationMethod;

    /**
     * Type Of Event
     *
     * @var string
     */
    protected $type;

    /**
     * Event Description
     *
     * @var string
     */
    protected $description;

    /**
     * Time Of Event
     *
     * @var \DateTime
     */
    protected $eventTime;

    /**
     * @var string
     */
    protected $user;

    public function __construct()
    {
        $this->setEventTime('now');
    }

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
     * @param mix $eventTime
     */
    public function setEventTime($eventTime)
    {
        $this->eventTime = new \DateTime($eventTime);
    }
}