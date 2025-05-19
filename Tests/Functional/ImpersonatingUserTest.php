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
use Symfony\Component\DomCrawler\Crawler;
use Xiidea\EasyAuditBundle\Tests\Functional\Bundle\TestBundle\Controller\DefaultController;

class ImpersonatingUserTest extends WebTestCase
{
    /** @var null|\Symfony\Bundle\FrameworkBundle\KernelBrowser */
    protected $client = null;

    protected static function createKernel(array $options = array()): ImpersonatingUserTestKernel
    {
        return new ImpersonatingUserTestKernel(
            $options['config'] ?? 'config',
            !isset($options['debug']) || (bool)$options['debug']
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSecuredEventWithImpersonatingUser()
    {
        $this->client = $this->createAuthenticatedClient();

        $name = 'simple.event';
        $this->client->request('GET', "/some-secure-url/{$name}", [], [], [
            "HTTP_AUTHORIZATION" => "Basic ".base64_encode("admin:login"),
        ]);
        $crawler = $this->client->request('GET', "/some-secure-url/{$name}?_switch_user=user");
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $event = $this->getEventArrayFromResponse($crawler);

        $this->assertEquals($name, $event['typeId']);
        $this->assertEquals($name, $event['type']);
        $this->assertEquals($name, $event['description']);
//        $this->assertEquals('admin', $event['impersonatingUser']);
        $this->assertEquals('user', $event['user']);
        $this->assertEquals('127.0.0.1', $event['ip']);
    }

    protected function createAuthenticatedClient()
    {
        $client = static::createClient(array('config' => 'switchuser'));

        $client->followRedirects(true);

        return $client;
    }

    /**
     * @param Crawler $crawler
     *
     * @return mixed
     */
    private function getEventArrayFromResponse(Crawler $crawler): array
    {
        $html = $crawler->html();
        $parts = explode(DefaultController::RESPONSE_BOUNDARY, $html);

        return unserialize($parts[1]);
    }
}
