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
    private $identity;

    /**
     * @var LifecycleEventArgs
     */
    private $lifecycleEventArgs;

    public function __construct(LifecycleEventArgs $lifecycleEventArgs, $identity)
    {
        $this->lifecycleEventArgs = $lifecycleEventArgs;
        $this->identity = $identity;
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
