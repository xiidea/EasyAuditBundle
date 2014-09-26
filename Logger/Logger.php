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

class Logger implements LoggerInterface
{

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

        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush($event);
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

}