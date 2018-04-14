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

use Doctrine\ORM\EntityManager;
use Xiidea\EasyAuditBundle\Entity\BaseAuditLog as AuditLog;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;

class Logger implements LoggerInterface
{
    private $entityDeleteLogs = [];

    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function log(AuditLog $event = null)
    {
        if(empty($event)) {
            return;
        }

        if($event->getTypeId() === DoctrineEvents::ENTITY_DELETED) {
            $this->entityDeleteLogs[] = $event;
            return;
        }

        $this->saveLog($event);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
         return $this->getDoctrine()->getManager();
    }

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @param AuditLog $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function saveLog(AuditLog $event)
    {
        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush($event);
    }

    public function savePendingLogs()
    {
        foreach ($this->entityDeleteLogs as $log) {
            $this->saveLog($log);
        }

        $this->entityDeleteLogs = [];
    }

}
