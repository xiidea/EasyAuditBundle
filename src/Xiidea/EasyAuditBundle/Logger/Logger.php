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
use Xiidea\EasyAuditBundle\Entity\AuditLog;
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

    public function log(AuditLog $event)
    {
        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush($event);
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