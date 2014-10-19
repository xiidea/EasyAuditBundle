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
use Symfony\Component\EventDispatcher\Event;

class DoctrineEntityEvent extends Event
{

    /**
     * @var \Doctrine\ORM\Event\LifecycleEventArgs
     */
    private $lifecycleEventArgs;

    public function __construct(LifecycleEventArgs $lifecycleEventArgs)
    {
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
