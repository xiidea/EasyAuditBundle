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

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Contracts\EventDispatcher\Event;

class DoctrineObjectEvent extends Event
{
    public function __construct(private LifecycleEventArgs $lifecycleEventArgs, private $identity)
    {
    }

    /**
     * @return LifecycleEventArgs
     */
    public function getLifecycleEventArgs()
    {
        return $this->lifecycleEventArgs;
    }

    /**
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->identity;
    }
}
