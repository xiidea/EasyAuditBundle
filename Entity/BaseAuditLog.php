<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Entity;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Xiidea\EasyAuditBundle\Traits\EntityHydrationMethod;

class BaseAuditLog
{
    use EntityHydrationMethod;

    /**
     * @var string
     */
    protected $typeId;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \DateTime
     */
    protected $eventTime;

    protected $user;

    protected $impersonatingUser;

    /**
     * @var String
     */
    protected $ip;

    /**
     * @var String
     */
    protected $level = LogLevel::INFO;

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
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
     * @return string
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param string $typeId
     *
     * @return $this
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * @return String
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param String $ip
     *
     * @return $this
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return string
     */
    final public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param string $level
     * @return $this
     */
    final public function setLevel($level)
    {
        if (!in_array(strtolower($level), $this->getAllowedLevel())) {
            throw new InvalidArgumentException();
        }

        $this->level = $level;

        return $this;
    }

    private function getAllowedLevel()
    {
        $oClass = new \ReflectionClass ('Psr\Log\LogLevel');

        return $oClass->getConstants();
    }

    /**
     * @return mixed
     */
    public function getImpersonatingUser()
    {
        return $this->impersonatingUser;
    }

    /**
     * @param mixed $impersonatingUser
     */
    public function setImpersonatingUser($impersonatingUser)
    {
        $this->impersonatingUser = $impersonatingUser;
    }
}
