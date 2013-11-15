<?php

/*
 * This file is part of the EasyAudit package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Traits;

trait ServiceGetterMethods
{
    /**
     * @return \Xiidea\EasyAuditBundle\Event\EventResolverInterface
     */
    public function getResolver()
    {
        return $this->container->get($this->container->getParameter('xiidea.easy_audit.resolver'));
    }

    /**
     * @return \Xiidea\EasyAuditBundle\Logger\LoggerInterface
     */
    public function getLogger()
    {
        return $this->container->get($this->container->getParameter('xiidea.easy_audit.logger'));
    }
} 