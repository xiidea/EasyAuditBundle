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

trait ServiceContainerGetterMethods
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @return \Xiidea\EasyAuditBundle\Resolver\EventResolverInterface
     */
    public function getCommonResolver()
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

    public function getService($serviceName)
    {
        return $this->container->get($serviceName);
    }

    public function getParameter($parameter)
    {
        return $this->container->getParameter('xiidea.easy_audit.'.$parameter);
    }

    /**
     * @return \Symfony\Component\HttpKernel\KernelInterface
     */
    public function getKernel()
    {
        return $this->container->get('kernel');
    }
} 