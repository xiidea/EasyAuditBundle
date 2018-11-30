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

use Xiidea\EasyAuditBundle\Document\BaseAuditLog as AuditLog;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;

class Logger implements LoggerInterface
{
    private $documentDeleteLogs = [];

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * Logger constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function log(AuditLog $event = null)
    {
        if ($event === null) {
            return;
        }

        if ($event->getTypeId() === DoctrineEvents::ENTITY_DELETED) {
            $this->documentDeleteLogs[] = $event;

            return;
        }

        $this->saveLog($event);
    }

    /**
     * @return ObjectManager
     */
    protected function getDocumentManager()
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
        $this->getDocumentManager()->persist($event);
        $this->getDocumentManager()->flush($event);
    }

    public function savePendingLogs()
    {
        foreach ($this->documentDeleteLogs as $log) {
            $this->saveLog($log);
        }

        $this->documentDeleteLogs = [];
    }
}
