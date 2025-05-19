<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Functional\Bundle\TestBundle\Logger;

use Symfony\Component\Filesystem\Filesystem;
use Xiidea\EasyAuditBundle\Model\BaseAuditLog as AuditLog;
use Xiidea\EasyAuditBundle\Logger\LoggerInterface;

class FileLogger implements LoggerInterface
{
    private $dir;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    public function log(?AuditLog $event = null)
    {
        if (empty($event)) {
            return;
        }

        $array = array(
            'typeId' => $event->getType(),
            'type' => $event->getTypeId(),
            'description' => $event->getDescription(),
            'user' => $event->getUser(),
            'impersonatingUser' => $event->getImpersonatingUser(),
            'ip' => $event->getIp(),
        );

        $fs = new Filesystem();

        $fs->mkdir($this->dir);

        file_put_contents($this->dir.DIRECTORY_SEPARATOR.'audit.log', serialize($array));
    }
}
