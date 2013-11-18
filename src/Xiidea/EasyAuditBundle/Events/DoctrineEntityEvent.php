<?php
/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Events;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Xiidea\EasyAuditBundle\Resolver\EventResolverInterface;

class DoctrineEntityEvent extends Event
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;
    /**
     * @var \Doctrine\ORM\Event\LifecycleEventArgs
     */
    private $lifecycleEventArgs;

    public function __construct(ContainerInterface $container, LifecycleEventArgs $lifecycleEventArgs)
    {
        $this->container = $container;
        $this->lifecycleEventArgs = $lifecycleEventArgs;
    }

    /**
     * @return LifecycleEventArgs
     */
    public function getLifecycleEventArgs()
    {
        return $this->lifecycleEventArgs;
    }
}