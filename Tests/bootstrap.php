<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

if (!class_exists('PHPUnit_Framework_TestCase') ||
    version_compare(PHPUnit_Runner_Version::id(), '3.6') < 0
) {
    die('PHPUnit framework is required, at least 3.6 version');
}

if (!class_exists('PHPUnit_Framework_MockObject_MockBuilder')) {
    die('PHPUnit MockObject plugin is required, at least 1.0.8 version');
}

date_default_timezone_set("Asia/Dhaka");

$vendor = realpath(__DIR__ . '/../vendor');

$loader = require($vendor.'/autoload.php');

