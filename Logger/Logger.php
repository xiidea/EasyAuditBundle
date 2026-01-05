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

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Xiidea\EasyAuditBundle\Model\BaseAuditLog as AuditLog;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;

class Logger implements LoggerInterface
{
    private $entityDeleteLogs = [];

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[\Override]
    public function log(?AuditLog $event = null)
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
     * @return ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @param AuditLog $event
     */
    protected function saveLog(AuditLog $event)
    {
        $this->getManager()->persist($event);
        $this->getManager()->flush($event);
    }

    public function savePendingLogs()
    {
        foreach ($this->entityDeleteLogs as $log) {
            $this->saveLog($log);
        }

        $this->entityDeleteLogs = [];
    }
}
