<?php

/*
 * This file is part of the EasyAudit package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Logger;

use Doctrine\ORM\EntityManager;
use Xiidea\EasyAuditBundle\Event\LogEventInterface;
use Xiidea\EasyAuditBundle\Repository\AuditLogRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Logger implements LoggerInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function log(LogEventInterface $event)
    {
        $auditLog = $this->getAuditLogRepository()->create(array(
            'type'=> $event->getType(),
            'user'=> $event->getUser(),
            'description'=> $event->getDescription()
        ));

        $this->getEntityManager()->persist($auditLog);
        $this->getEntityManager()->flush($auditLog);
    }

    /**
     * @return AuditLogRepository
     */
    protected function getAuditLogRepository()
    {
        return $this->getEntityManager()->getRepository('XiideaEasyAuditBundle:AuditLog');
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
         return $this->container->get('doctrine')
             ->getManager();
    }
} 