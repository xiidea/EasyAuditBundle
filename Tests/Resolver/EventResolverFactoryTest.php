<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Resolver;


use Symfony\Component\EventDispatcher\Event;
use Xiidea\EasyAuditBundle\Resolver\DefaultEventResolver;
use Xiidea\EasyAuditBundle\Resolver\EventResolverFactory;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\AuditObjectResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\CustomEventResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\DummyToken;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\InvalidEventInfoResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\InvalidEventResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\NullResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\Basic;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\EntityEvent;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\WithEmbeddedResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\UserEntity;

class EventResolverFactoryTest extends \PHPUnit_Framework_TestCase {

    /** @var  \PHPUnit_Framework_MockObject_MockObject  */
    private $container;

    /** @var  \PHPUnit_Framework_MockObject_MockObject  */
    private $kernel;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $securityContext;

    /** @var  EventResolverFactory */
    private $resolverFactory;

    /** @var  Event */
    private $event;

    public function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $this->resolverFactory =  new EventResolverFactory();
        $this->resolverFactory->setContainer($this->container);
    }

    public function testDefaultEventResolver() {
        $this->event = new Basic('basic');

        $this->container->expects($this->at(0))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'))
            ->willReturn(array());

        $this->container->expects($this->at(1))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.resolver'))
            ->willReturn('xiidea.easy_audit.default_event_resolver');

        $this->container->expects($this->at(2))
            ->method('get')
            ->with($this->equalTo('xiidea.easy_audit.default_event_resolver'))
            ->willReturn(new DefaultEventResolver());

        $this->container->expects($this->at(3))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.entity_class'))
            ->willReturn('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog');

        $this->mockClientIpResolver(4);

        $this->container->expects($this->at(5))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.user_property'))
            ->willReturn('username');

        $this->initiateContainerWithSecurityContext(6);

        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));


        $auditLog = $this->resolverFactory->getEventLog($this->event);

        $this->assertEventInfo($auditLog, array(
            'name'=> 'basic',
            'description' => 'basic',
            'user' => 'admin',
            'ip'=>'127.0.0.1'
        ));
    }

    public function testInvalidResolverWithDebugOff() {
        $this->event = new Basic('basic');

        $this->container->expects($this->at(0))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'))
            ->willReturn(array());

        $this->container->expects($this->at(1))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.resolver'))
            ->willReturn('xiidea.easy_audit.default_event_resolver');

        $this->container->expects($this->at(2))
            ->method('get')
            ->with($this->equalTo('xiidea.easy_audit.default_event_resolver'))
            ->willReturn(new InvalidEventInfoResolver());

        $this->container->expects($this->at(3))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.entity_class'))
            ->willReturn('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog');

        $this->initiateContainerWithDebugMode(false, 4);

        $auditLog = $this->resolverFactory->getEventLog($this->event);
        $this->assertNull($auditLog);
    }

    /**
     * @expectedException \Xiidea\EasyAuditBundle\Exception\UnrecognizedEventInfoException
     */
    public function testInvalidResolverWithDebugOn() {
        $this->event = new Basic('basic');

        $this->container->expects($this->at(0))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'))
            ->willReturn(array());

        $this->container->expects($this->at(1))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.resolver'))
            ->willReturn('xiidea.easy_audit.default_event_resolver');

        $this->container->expects($this->at(2))
            ->method('get')
            ->with($this->equalTo('xiidea.easy_audit.default_event_resolver'))
            ->willReturn(new InvalidEventInfoResolver());

        $this->container->expects($this->at(3))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.entity_class'))
            ->willReturn('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog');

        $this->initiateContainerWithDebugMode(true, 4);

        $auditLog = $this->resolverFactory->getEventLog($this->event);
        $this->assertNull($auditLog);
    }

    public function testAuditLogObjectEventResolver() {
        $this->event = new Basic('basic');

        $this->container->expects($this->at(0))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'))
            ->willReturn(array());

        $this->container->expects($this->at(1))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.resolver'))
            ->willReturn('xiidea.easy_audit.default_event_resolver');

        $this->container->expects($this->at(2))
            ->method('get')
            ->with($this->equalTo('xiidea.easy_audit.default_event_resolver'))
            ->willReturn(new AuditObjectResolver());

        $this->container->expects($this->at(3))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.entity_class'))
            ->willReturn('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog');

        $this->mockClientIpResolver(4);

        $this->container->expects($this->at(5))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.user_property'))
            ->willReturn('username');

        $this->initiateContainerWithSecurityContext(6);

        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));


        $auditLog = $this->resolverFactory->getEventLog($this->event);

        $this->assertEventInfo($auditLog, array(
            'name'=> 'basic',
            'description'=> 'basic',
            'user' => 'admin',
            'ip'=>'127.0.0.1'
        ));

    }

    public function testEmbeddedEventResolver() {
        $this->event = new WithEmbeddedResolver('embedded');

        $this->container->expects($this->at(0))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.entity_class'))
            ->willReturn('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog');

        $this->mockClientIpResolver(1);

        $this->container->expects($this->at(2))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.user_property'))
            ->willReturn('username');

        $this->initiateContainerWithSecurityContext(3);

        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));


        $auditLog = $this->resolverFactory->getEventLog($this->event);

        $this->assertEventInfo($auditLog, array(
            'name'=> 'embedded',
            'description'=> 'It is an embedded event',
            'user' => 'admin',
            'ip'=>'127.0.0.1'
        ));
    }

    public function testNullEventInfo() {
        $this->event = new Basic('basic');

        $this->container->expects($this->at(0))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'))
            ->willReturn(array());

        $this->container->expects($this->at(1))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.resolver'))
            ->willReturn('xiidea.easy_audit.default_event_resolver');

        $this->container->expects($this->at(2))
            ->method('get')
            ->with($this->equalTo('xiidea.easy_audit.default_event_resolver'))
            ->willReturn(new NullResolver());

        $this->container->expects($this->at(3))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.entity_class'))
            ->willReturn('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog');

        $auditLog = $this->resolverFactory->getEventLog($this->event);
        $this->assertNull($auditLog);
    }

    public function testEntityEventResolver()
    {
        $this->event = new EntityEvent();

        $this->container->expects($this->at(0))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'))
            ->willReturn(array());

        $this->container->expects($this->at(1))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.entity_event_resolver'))
            ->willReturn('xiidea.easy_audit.default_entity_event_resolver');

        $this->container->expects($this->at(2))
            ->method('get')
            ->with($this->equalTo('xiidea.easy_audit.default_entity_event_resolver'))
            ->willReturn(new DefaultEventResolver());

        $this->container->expects($this->at(3))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.entity_class'))
            ->willReturn('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog');

        $this->mockClientIpResolver(4);

        $this->container->expects($this->at(5))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.user_property'))
            ->willReturn('username');

        $this->initiateContainerWithSecurityContext(6);

        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));


        $auditLog = $this->resolverFactory->getEventLog($this->event);

        $this->assertEventInfo($auditLog,
            array(
                'description' => 'easy_audit.doctrine.entity.created',
                'name' => 'easy_audit.doctrine.entity.created',
                'user' => 'admin',
                'ip' => '127.0.0.1',
            ));
    }

    public function testCustomValidEventResolver()
    {
        $this->event = new Basic('basic');

        $this->container->expects($this->at(0))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'))
            ->willReturn(array('basic' => 'xiidea.easy_audit.custom_event_resolver'));

        $this->container->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('xiidea.easy_audit.custom_event_resolver'))
            ->willReturn(new CustomEventResolver());

        $this->container->expects($this->at(2))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.entity_class'))
            ->willReturn('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog');

        $this->mockClientIpResolver(3);

        $this->container->expects($this->at(4))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.user_property'))
            ->willReturn('username');

        $this->initiateContainerWithSecurityContext(5);

        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));


        $auditLog = $this->resolverFactory->getEventLog($this->event);

        $this->assertEventInfo($auditLog,
            array(
                'description' => 'Custom description',
                'name' => 'basic',
                'user' => 'admin',
                'ip' => '127.0.0.1',
            ));
    }

    public function testCustomInValidEventResolverWithDebugOff()
    {
        $i=0;

        $this->event = new Basic('basic');
        $this->container->expects($this->at($i++))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'))
            ->willReturn(array('basic' => 'xiidea.easy_audit.custom_event_resolver'));

        $this->container->expects($this->at($i++))
            ->method('get')
            ->with($this->equalTo('xiidea.easy_audit.custom_event_resolver'))
            ->willReturn(new InvalidEventResolver());

        $this->initiateContainerWithDebugMode(false, $i++);

        $this->container->expects($this->at($i))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.entity_class'))
            ->willReturn('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog');

        $auditLog = $this->resolverFactory->getEventLog($this->event);
        $this->assertNull($auditLog);

    }

    /**
     * @expectedException \Xiidea\EasyAuditBundle\Exception\InvalidServiceException
     */
    public function testCustomInValidEventResolverWithDebugOn()
    {
        $i=0;

        $this->event = new Basic('basic');
        $this->container->expects($this->at($i++))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'))
            ->willReturn(array('basic' => 'xiidea.easy_audit.custom_event_resolver'));

        $this->container->expects($this->at($i++))
            ->method('get')
            ->with($this->equalTo('xiidea.easy_audit.custom_event_resolver'))
            ->willReturn(new InvalidEventResolver());

        $this->initiateContainerWithDebugMode(true, $i);

        $auditLog = $this->resolverFactory->getEventLog($this->event);
        $this->assertNull($auditLog);

    }

    public function testEventTriggeredFromConsoleCommand() {
        $this->event = new Basic('basic');

        $this->container->expects($this->at(0))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.custom_resolvers'))
            ->willReturn(array());

        $this->container->expects($this->at(1))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.resolver'))
            ->willReturn('xiidea.easy_audit.default_event_resolver');

        $this->container->expects($this->at(2))
            ->method('get')
            ->with($this->equalTo('xiidea.easy_audit.default_event_resolver'))
            ->willReturn(new DefaultEventResolver());

        $this->container->expects($this->at(3))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.entity_class'))
            ->willReturn('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog');

        $this->mockClientIpResolver(4, true);

        $this->container->expects($this->at(5))
            ->method('getParameter')
            ->with($this->equalTo('xiidea.easy_audit.user_property'))
            ->willReturn('username');

        $this->initiateContainerWithSecurityContext(6);

        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->willReturn(null);


        $auditLog = $this->resolverFactory->getEventLog($this->event);

        $this->assertEventInfo($auditLog,
            array(
                'description' => 'basic',
                'name' => 'basic',
                'user' => 'By Command',
                'type' => 'easy_audit.doctrine.entity.created',
                'ip' => '',
            ));
    }

    /**
     * @param bool $on
     * @param int $callIndex
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function initiateContainerWithDebugMode($on = true, $callIndex = 0)
    {
        $this->kernel->expects($this->once())
            ->method('isDebug')
            ->will($this->returnValue($on));

        $this->container->expects($this->at($callIndex))
            ->method('get')
            ->with($this->equalTo('kernel'))
            ->will($this->returnValue($this->kernel));
    }

    protected function initiateContainerWithSecurityContext($callIndex = 0)
    {
        $this->securityContext = $this
            ->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();

        $this->container->expects($this->at($callIndex))
            ->method('has')
            ->with($this->equalTo('security.context'))
            ->willReturn(true);

        $this->container->expects($this->at($callIndex + 1))
            ->method('get')
            ->with($this->equalTo('security.context'))
            ->willReturn($this->securityContext);
    }

    private function mockClientIpResolver($callIndex, $fromConsole = false)
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        if ($fromConsole) {
            $request->expects($this->once())
                ->method('getClientIp')
                ->willThrowException(new \Exception());
        } else {
            $request->expects($this->once())
                ->method('getClientIp')
                ->willReturn('127.0.0.1');
        }


        $this->container->expects($this->at($callIndex))
            ->method('get')
            ->with($this->equalTo('request'))
            ->willReturn($request);
    }

    /**
     * @param AuditLog $auditLog
     * @param array $expected
     */
    private function assertEventInfo(AuditLog $auditLog, array $expected)
    {
        $this->assertNotNull($auditLog);
        $this->assertEquals($expected['description'], $auditLog->getDescription());
        $this->assertEquals($expected['name'], $auditLog->getType());
        $this->assertEquals($expected['name'], $auditLog->getTypeId());
        $this->assertEquals($expected['user'], $auditLog->getUser());
        $this->assertEquals($expected['ip'], $auditLog->getIp());
        $this->assertNotNull($auditLog->getEventTime());
    }
}
 