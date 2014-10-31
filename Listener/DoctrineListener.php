<?php
/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Xiidea\EasyAuditBundle\Common\UserAwareComponent;
use Xiidea\EasyAuditBundle\Entity\BaseAuditLog;

class DoctrineListener extends UserAwareComponent
{
    /**
     * @var array
     */
    protected $entityClass;

    public function __construct($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof BaseAuditLog) {
            $entity->setEventTime(new \DateTime());
            $this->setUser($entity);
        }
    }

    protected function setUser(BaseAuditLog $entity)
    {
        $userProperty = $this->container->getParameter('xiidea.easy_audit.user_property');

        $user = $this->getUser();

        if (empty($userProperty)) {
            $entity->setUser($user);
        } elseif ($user && is_callable(array($user, "get{$userProperty}"))) {
            $propertyGetter = "get{$userProperty}";
            $entity->setUser($user->$propertyGetter());
        } elseif ($user === NULL) {
            $entity->setUser($this->getUsername());
        } elseif ($this->isDebug()) {
            throw new \Exception("get{$userProperty}() not found in user object");
        }
    }

    protected function isDebug()
    {
        return $this->container->get('kernel')->isDebug();
    }
}
