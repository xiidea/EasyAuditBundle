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

class LoggerFactory
{
    static private $loggers = array();

    public function executeLoggers($eventInfo)
    {
        foreach (self::$loggers as $logger) {
            if ($logger instanceof LoggerInterface) {
                $logger->log($eventInfo);
            }
        }
    }

    public function addLogger($loggerName, $logger)
    {
        self::$loggers[$loggerName] = $logger;
    }
}