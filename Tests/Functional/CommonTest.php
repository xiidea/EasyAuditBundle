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
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\Basic;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\WithEmbeddedResolver;
use Xiidea\EasyAuditBundle\Tests\Functional\Bundle\TestBundle\Controller\DefaultController;

class CommonTest extends BaseTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testEntityClassShouldBeAnInstanceOfBaseAuditLog()
    {
        $kernel = self::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        $entityClass = $container->getParameter('xiidea.easy_audit.entity_class');

        $this->assertInstanceOf('\Xiidea\EasyAuditBundle\Entity\BaseAuditLog', (new $entityClass));
    }

    /**
     * @runInSeparateProcess
     */
    public function testDispatch()
    {
        $kernel = self::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        $name = 'simple.event';

        $container->get('event_dispatcher')->dispatch($name,
            new Basic()
        );

        $logFile = realpath($container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . "audit.log");

        $event = unserialize(file_get_contents($logFile));
        $this->assertEquals($name, $event['typeId']);
        $this->assertEquals($name, $event['type']);
        $this->assertEquals($name, $event['description']);
        $this->assertEquals('By Command', $event['user']);
        $this->assertEquals('', $event['ip']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testMultipleChanel()
    {
        $kernel = self::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        $name = 'simple.event';

        $container->get('event_dispatcher')->dispatch($name,
            new Basic()
        );

        $container->get('event_dispatcher')->dispatch($name."2",
            new WithEmbeddedResolver($name."2")
        );

        $logFile = realpath($container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . "audit.log");
        $logFile2 = realpath($container->getParameter('kernel.cache_dir') . "2" . DIRECTORY_SEPARATOR . "audit.log");

        $event2 = unserialize(file_get_contents($logFile2));
        $event = unserialize(file_get_contents($logFile));

        $this->assertEquals($name, $event['typeId']);
        $this->assertEquals($name, $event['type']);
        $this->assertEquals($name, $event['description']);
        $this->assertEquals('By Command', $event['user']);
        $this->assertEquals('', $event['ip']);

        $this->assertEquals($name."2", $event2['typeId']);
        $this->assertEquals($name."2", $event2['type']);
        $this->assertEquals("It is an embedded event", $event2['description']);
        $this->assertEquals('By Command', $event2['user']);
        $this->assertEquals('', $event2['ip']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSecuredEventWithUserLogin()
    {
        $this->logIn();
        $name = 'simple.event';
        $crawler = $this->client->request('GET', "/some-secure-url/{$name}");
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $event = $this->getEventArrayFromResponse($crawler);

        $this->assertEquals($name, $event['typeId']);
        $this->assertEquals($name, $event['type']);
        $this->assertEquals($name, $event['description']);
        $this->assertEquals('admin', $event['user']);
        $this->assertEquals('127.0.0.1', $event['ip']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testEventOnPublicUrlWithUserLogin()
    {
        $this->logIn();

        $name = 'simple.event';
        $crawler = $this->client->request('GET', "/public/some-public-url/{$name}");
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $event = $this->getEventArrayFromResponse($crawler);

        $this->assertEquals($name, $event['typeId']);
        $this->assertEquals($name, $event['type']);
        $this->assertEquals($name, $event['description']);

        $this->assertEquals('admin', $event['user']);
        $this->assertEquals('127.0.0.1', $event['ip']);

    }

    /**
     * @runInSeparateProcess
     */
    public function testEventOnPublicUrlWithoutUserLogin()
    {
        $name = 'simple.event';
        $crawler = $this->client->request('GET', "/public/some-public-url/{$name}");
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $event = $this->getEventArrayFromResponse($crawler);

        $this->assertEquals($name, $event['typeId']);
        $this->assertEquals($name, $event['type']);
        $this->assertEquals($name, $event['description']);
        $this->assertEquals('Anonymous', $event['user']);
        $this->assertEquals('127.0.0.1', $event['ip']);
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
