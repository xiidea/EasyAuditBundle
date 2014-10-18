<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!class_exists('PHPUnit_Framework_TestCase') ||
    version_compare(PHPUnit_Runner_Version::id(), '3.6') < 0
) {
    die('PHPUnit framework is required, at least 3.6 version');
}

if (!class_exists('PHPUnit_Framework_MockObject_MockBuilder')) {
    die('PHPUnit MockObject plugin is required, at least 1.0.8 version');
}

define('TESTS_PATH', __DIR__);
define('TESTS_TEMP_DIR', __DIR__.'/temp');
define('VENDOR_PATH', realpath(__DIR__ . '/../vendor'));

$loader = require(VENDOR_PATH.'/autoload.php');

Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
    VENDOR_PATH.'/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
);

$reader = new \Doctrine\Common\Annotations\AnnotationReader();
$reader = new \Doctrine\Common\Annotations\CachedReader($reader, new \Doctrine\Common\Cache\ArrayCache());
$_ENV['annotation_reader'] = $reader;