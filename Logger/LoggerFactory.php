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

class LoggerFactory extends ContainerAware
{
    use ServiceContainerGetterMethods;

    static private $loggers = array();

    private $loggersChanel;

    public function __construct(array $chanel = array())
    {
        $this->loggersChanel = $chanel;
    }

    /**
     * @param null|\Xiidea\EasyAuditBundle\Entity\BaseAuditLog $eventInfo
     */
    public function executeLoggers($eventInfo)
    {
        if (empty($eventInfo)) {
            return;
        }

        foreach (self::$loggers as $id => $logger) {
            if ($logger instanceof LoggerInterface && $this->isChanelRegisterWithLogger($id, $eventInfo->getLevel())) {
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
        } elseif ($this->isDebug()) {
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

    /**
     * @param string $id
     * @param string $level
     * @return bool
     */
    private function isChanelRegisterWithLogger($id, $level)
    {
        if (!isset($this->loggersChanel[$id])) {
            return true;
        }

        if ($this->loggersChanel[$id]['type'] == 'inclusive') {
            return in_array($level, $this->loggersChanel[$id]['elements']);
        }

        if ($this->loggersChanel[$id]['type'] == 'exclusive') {
            return !in_array($level, $this->loggersChanel[$id]['elements']);
        }

        return false;
    }
}
