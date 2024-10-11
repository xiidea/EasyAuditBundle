<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Xiidea\EasyAuditBundle\Logger\Logger;
use Symfony\Component\Console\ConsoleEvents;

class DoctrineDeleteEventLogger implements EventSubscriberInterface
{
    /**
     * DoctrineDeleteEventLogger constructor.
     *
     * @param Logger $logger
     */
    public function __construct(private Logger $logger)
    {
    }

    public function savePendingLogs()
    {
        $this->logger->savePendingLogs();
    }

    /**
     * @return array
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::TERMINATE => 'savePendingLogs',
            KernelEvents::TERMINATE => 'savePendingLogs',
        ];
    }
}
