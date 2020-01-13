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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseTestCase extends WebTestCase
{
    /** @var null|\Symfony\Bundle\FrameworkBundle\KernelBrowser */
    protected $client = null;

    protected static function createKernel(array $options = array())
    {
        return new TestKernel(
            isset($options['config']) ? $options['config'] : 'config',
            isset($options['debug']) ? (bool) $options['debug'] : true
        );
    }

    protected function createDefaultClient()
    {
        $this->client = static::createClient();
    }

    protected function logIn()
    {
        $this->client = static::createClient(array(), [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'login',
        ]
        );
    }
}
