<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Logger;

use Doctrine\Common\Persistence\ManagerRegistry;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;
use Xiidea\EasyAuditBundle\Model\BaseAuditLog as AuditLog;

class Logger implements LoggerInterface
{
    private $entityDeleteLogs = [];

    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function log(AuditLog $event = null)
    {
        if (empty($event)) {
            return;
        }

        if (DoctrineEvents::ENTITY_DELETED === $event->getTypeId()) {
            $this->entityDeleteLogs[] = $event;

            return;
        }

        $this->saveLog($event);
    }

    /**
     * @param AuditLog $event
     */
    protected function saveLog(AuditLog $event)
    {
        $manager = $this->doctrine->getManagerForClass($event);
        $manager->persist($event);
        $manager->flush($event);
    }

    public function savePendingLogs()
    {
        foreach ($this->entityDeleteLogs as $log) {
            $this->saveLog($log);
        }

        $this->entityDeleteLogs = [];
    }
}
