<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Xiidea\EasyAuditBundle\Tests\Functional\Bundle\TestBundle\XiideaTestBundle;
use Xiidea\EasyAuditBundle\XiideaEasyAuditBundle;

class TestKernel extends Kernel
{
    private $config;

    public function __construct($config, $debug)
    {
        parent::__construct('test', $debug);

        $fs = new Filesystem();
        if (!$fs->isAbsolutePath($config)) {
            $config = __DIR__ . '/config/' . $config . ".yml";
        }

        if (!file_exists($config)) {
            throw new \RuntimeException(sprintf('The config file "%s" does not exist.', $config));
        }

        $this->config = $config;
    }

    public function registerBundles()
    {
        return array(
            new FrameworkBundle(),
            new SecurityBundle(),
            new XiideaEasyAuditBundle(),
            new XiideaTestBundle()
        );
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/XiideaEasyAuditBundle/cache/'.substr(sha1($this->config), 0, 6);
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/XiideaEasyAuditBundle/logs/'.substr(sha1($this->config), 0, 6);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->config);
    }
}
