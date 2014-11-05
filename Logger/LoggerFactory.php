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

use Symfony\Component\DependencyInjection\ContainerAware;
use Xiidea\EasyAuditBundle\Exception\InvalidServiceException;
use Xiidea\EasyAuditBundle\Traits\ServiceContainerGetterMethods;

class LoggerFactory  extends ContainerAware
{
    use ServiceContainerGetterMethods;

    static private $loggers = array();

    /**
     * @param null|\Xiidea\EasyAuditBundle\Entity\BaseAuditLog $eventInfo
     */
    public function executeLoggers($eventInfo)
    {
        if(empty($eventInfo)) {
            return;
        }

        foreach (self::$loggers as $logger) {
            if ($logger instanceof LoggerInterface) {
                $logger->log($eventInfo);
            }
        }
    }

    /**
     * @param string $loggerName
     * @param $logger
     * @throws InvalidServiceException
     */
    public function addLogger($loggerName, $logger)
    {
        if ($logger instanceof LoggerInterface) {
            self::$loggers[$loggerName] = $logger;
        } elseif($this->getKernel()->isDebug()) {
            throw new InvalidServiceException('Logger Service must implement' . __NAMESPACE__ . "LoggerInterface");
        }
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }
}
