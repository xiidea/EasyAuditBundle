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

use Symfony\Component\DomCrawler\Crawler;
use Xiidea\EasyAuditBundle\Tests\Functional\Bundle\TestBundle\Controller\DefaultController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ImpersonatingUserTest extends WebTestCase
{
    /** @var null|\Symfony\Bundle\FrameworkBundle\Client */
    protected $client = null;

    static protected function createKernel(array $options = array())
    {
        return new ImpersonatingUserTestKernel(
            isset($options['config']) ? $options['config'] : 'config',
            isset($options['debug']) ? (boolean)$options['debug'] : true
        );
    }

    /**
     *  @group failing
     * @runInSeparateProcess
     */
    public function testSecuredEventWithImpersonatingUser()
    {
         $this->client = $this->createAuthenticatedClient('admin');

        $name = 'simple.event';
        $crawler = $this->client->request('GET', "/some-secure-url/{$name}?_switch_user=user");
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->assertEquals('user', $this->client->getProfile()->getCollector('security')->getUser());

        $event = $this->getEventArrayFromResponse($crawler);

        $this->assertEquals($name, $event['typeId']);
        $this->assertEquals($name, $event['type']);
        $this->assertEquals($name, $event['description']);
        $this->assertEquals('admin', $event['impersonatingUser']);
        $this->assertEquals('user', $event['user']);
        $this->assertEquals('127.0.0.1', $event['ip']);
    }

    protected function createAuthenticatedClient($username)
    {
        $client = $this->createClient(array('config' => 'switchuser'));
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('login')->form();

        $form['_username'] = $username;
        $form['_password'] = 'login';
        $client->submit($form);

        return $client;
    }

    /**
     * @param Crawler $crawler
     * @return mixed
     */
    private function getEventArrayFromResponse(Crawler $crawler)
    {
        $html = $crawler->html();
        $parts = explode(DefaultController::RESPONSE_BOUNDARY, $html);
        $event = unserialize($parts[1]);
        return $event;
    }
}
