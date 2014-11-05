<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
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
     * @throws \Exception
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected abstract function getContainer();

    /**
     * @return \Xiidea\EasyAuditBundle\Resolver\EventResolverInterface
     */
    public function getCommonResolver()
    {
        return $this->getContainer()->get($this->getContainer()->getParameter('xiidea.easy_audit.resolver'));
    }


    public function getService($serviceName)
    {
        return $this->getContainer()->get($serviceName);
    }

    /**
     * @return \Xiidea\EasyAuditBundle\Resolver\EventResolverInterface
     */
    public function getEntityEventResolver()
    {
        return $this->getContainer()->get($this->getContainer()->getParameter('xiidea.easy_audit.entity_event_resolver'));
    }


    /**
     * @param string $parameter
     * @return mixed
     */
    public function getParameter($parameter)
    {
        return $this->getContainer()->getParameter('xiidea.easy_audit.'.$parameter);
    }

    /**
     * @return \Symfony\Component\HttpKernel\KernelInterface
     */
    public function getKernel()
    {
        return $this->getContainer()->get('kernel');
    }

    public function getDoctrineEventsList()
    {
        $reflectionClass = new \ReflectionClass('Xiidea\EasyAuditBundle\Events\DoctrineEvents');
        return  $reflectionClass->getConstants();
    }
}
