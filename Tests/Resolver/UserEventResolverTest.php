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


use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\EventDispatcher\Event;
use Xiidea\EasyAuditBundle\Resolver\UserEventResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\Basic;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\UserEntity;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\DummyToken;

class UserEventResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $container;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $securityContext;

    /** @var  UserEventResolver */
    private $eventResolver;

    public function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->eventResolver = new UserEventResolver();
        $this->eventResolver->setContainer($this->container);

    }

    public function testIsAnInstanceOfEventResolverInterface()
    {
        $this->assertInstanceOf('Xiidea\EasyAuditBundle\Resolver\EventResolverInterface', $this->eventResolver);
    }

    /**
     * @expectedException \LogicException
     */
    public function testWithoutSecurityBundle()
    {
        $this->container->expects($this->at(0))
            ->method('has')
            ->with($this->equalTo('security.context'))
            ->willReturn(false);

        $auditLog = $this->eventResolver->getEventLogInfo(new Basic(), 'security.interactive_login');

        $this->assertNull($auditLog);
    }

    public function testUnlistedEvent()
    {
        $auditLog = $this->eventResolver->getEventLogInfo(new Basic(), 'any_random_event');
        $this->assertEquals(array('type' => 'any_random_event', 'description' => 'any_random_event'), $auditLog);
    }

    public function testPasswordChangedEvent()
    {
        $event = $this->initializeMockEvent('FOS\UserBundle\Event\FilterUserResponseEvent');

        $event->expects($this->once())
            ->method('getUser')
            ->willReturn(new UserEntity());

        $auditLog = $this->eventResolver->getEventLogInfo($event, 'fos_user.change_password.edit.completed');

        $this->assertNotNull($auditLog);

        $this->assertEquals(array(
            'description' => "Password of user 'admin' Changed Successfully",
            'type' => "Password Changed"
        ), $auditLog);

    }

    public function testLoginEvent()
    {
        $this->initiateContainerWithSecurityContext();
        $event = $this->initializeMockEvent('FOS\UserBundle\Event\FilterUserResponseEvent');

        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));

        $auditLog = $this->eventResolver->getEventLogInfo($event, 'security.interactive_login');

        $this->assertEquals(array (
            'description' => "User 'admin' Logged in Successfully",
            'type' => 'User Logged in',
        ),$auditLog);
    }

    public function testLoginUsingRememberMeService() {
        $event = $this->initializeMockEvent('FOS\UserBundle\Event\UserEvent');

        $event->expects($this->once())
            ->method('getUser')
            ->willReturn(new UserEntity());

        $auditLog = $this->eventResolver->getEventLogInfo($event, 'fos_user.security.implicit_login');

        $this->assertEquals(array (
            'description' => "User 'admin' Logged in Successfully using remember me service",
            'type' => 'User Logged in',
        ),$auditLog);
    }

    public function testAuthenticationFailureEvent()
    {
        $event = $this->initializeMockEvent('Symfony\Component\Security\Core\Event\AuthenticationFailureEvent');

        $event->expects($this->once())
            ->method('getAuthenticationToken')
            ->willReturn(new DummyToken());

        $auditLog = $this->eventResolver->getEventLogInfo($event, 'security.authentication.failure');

        $this->assertNotNull($auditLog);

        $this->assertEquals(array(
            'description' => "Bad credentials Username: user",
            'type' => "Authentication Failed"
        ), $auditLog);

    }


    protected function initiateContainerWithSecurityContext()
    {
        $this->securityContext = $this
            ->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();

        $this->container->expects($this->at(0))
            ->method('has')
            ->with($this->equalTo('security.context'))
            ->willReturn(true);

        $this->container->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('security.context'))
            ->willReturn($this->securityContext);
    }

    /**
     * @param $eventClass
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function initializeMockEvent($eventClass)
    {
        $event = $this
            ->getMockBuilder($eventClass)
            ->disableOriginalConstructor()
            ->getMock();

        return $event;
    }
}
 