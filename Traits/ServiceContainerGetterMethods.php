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
        return $this->getService($this->getParameter('resolver'));
    }


    public function getService($serviceName)
    {
        return $this->getContainer()->get($serviceName);
    }

    /**
     * @return boolean|\Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        try {
            return $this->getService('request_stack')->getCurrentRequest();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return \Xiidea\EasyAuditBundle\Resolver\EventResolverInterface
     */
    public function getEntityEventResolver()
    {
        return $this->getService($this->getParameter('entity_event_resolver'));
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
        return $this->getService('kernel');
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return $this->getKernel()->isDebug();
    }

    public function getDoctrineEventsList()
    {
        $reflectionClass = new \ReflectionClass('Xiidea\EasyAuditBundle\Events\DoctrineEvents');
        return  $reflectionClass->getConstants();
    }
}
