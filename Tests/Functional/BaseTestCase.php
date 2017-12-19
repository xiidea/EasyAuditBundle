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

use Symfony\Component\Filesystem\Filesystem;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseTestCase extends WebTestCase
{
    /** @var null|\Symfony\Bundle\FrameworkBundle\Client */
    protected $client = null;

    static protected function createKernel(array $options = array())
    {
        return new TestKernel(
            isset($options['config']) ? $options['config'] : 'config',
            isset($options['debug']) ? (boolean)$options['debug'] : true
        );
    }

    protected function tearDown()
    {
        $this->cleanTmpDir();
    }

    protected function setUp()
    {
        $this->client = static::createClient();
        $this->cleanTmpDir();
    }

    private function cleanTmpDir()
    {
        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir() . '/XiideaEasyAuditBundle');
    }

    protected function logIn()
    {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'login',
        ));
    }
}
