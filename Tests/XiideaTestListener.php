<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests;

use PHPUnit\Framework\BaseTestListener;

if (class_exists('Symfony\Bridge\PhpUnit\SymfonyTestsListener')) {
    class_alias('Symfony\Bridge\PhpUnit\SymfonyTestsListener', 'Xiidea\EasyAuditBundle\Tests\XiideaTestListener');
} else {
    class XiideaTestListener extends BaseTestListener
    {
    }
}
