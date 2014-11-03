<?php

namespace Xiidea\EasyAuditBundle\Tests\Functional;

use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\Basic;

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
            new Basic($name)
        );

        $logdir = $container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . "audit.log";

        $this->assertStringEqualsFile($logdir, md5('simple.eventsimple.event'));
    }
}
