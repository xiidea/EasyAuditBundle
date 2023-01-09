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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Xiidea\EasyAuditBundle\Exception\InvalidServiceException;
use Xiidea\EasyAuditBundle\Exception\UnrecognizedEventInfoException;
use Xiidea\EasyAuditBundle\Resolver\DefaultEventResolver;
use Xiidea\EasyAuditBundle\Resolver\EventResolverFactory;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\AuditObjectResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\CustomEventResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\DummyToken;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\InvalidEventInfoResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\InvalidEventResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\NullResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\Basic;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\DoctrineEvent;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\WithEmbeddedResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\AuditLog;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\UserEntity;

class EventResolverFactoryTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $tokenStorage;

    /** @var EventResolverFactory */
    private $resolverFactory;

    /** @var Event */
    private $event;

    public function setUp(): void    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->resolverFactory = new EventResolverFactory(array(), 'username', AuditLog::class);
        $this->resolverFactory->setTokenStorage($this->tokenStorage);
    }

    public function testDefaultEventResolver()
    {
        $this->event = new Basic();

        $this->resolverFactory->setCommonResolver(new DefaultEventResolver());
        $this->mockClientIpResolverForBrowserRequest();

        $this->mockSecurityAuthChecker();

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'basic');

        $this->assertEventInfo($auditLog, array(
            'name' => 'basic',
            'description' => 'basic',
            'user' => 'admin',
            'impersonatingUser' => null,
            'ip' => '127.0.0.1',
        ));
    }

    public function testImpersonatingUser()
    {
        $this->event = new Basic();

        $this->resolverFactory->setCommonResolver(new DefaultEventResolver());

        $this->mockClientIpResolverForBrowserRequest();

        $this->mockSecurityAuthChecker(true);

        $userToken = new DummyToken(new UserEntity(2, 'admin'));

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(new SwitchUserToken(new UserEntity(1, 'a'), 'main', [], $userToken));

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'basic');

        $this->assertEventInfo($auditLog, array(
            'name' => 'basic',
            'description' => 'basic',
            'user' => 'a',
            'impersonatingUser' => 'admin',
            'ip' => '127.0.0.1',
        ));
    }

    public function testInvalidResolverWithDebugOff()
    {
        $this->event = new Basic();
        $this->initiateContainerWithDebugMode(false);
        $this->resolverFactory->setCommonResolver(new InvalidEventInfoResolver());

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'basic');
        $this->assertNull($auditLog);
    }

    public function testInvalidCustomResolverWithDebugOff()
    {
        $this->event = new Basic();
        $this->resolverFactory = new EventResolverFactory(array('custom' => 'r1'), 'username', AuditLog::class);
        $this->resolverFactory->setTokenStorage($this->tokenStorage);
        $this->initiateContainerWithDebugMode(false);

        $this->resolverFactory->setCommonResolver(new DefaultEventResolver());
        $this->resolverFactory->addCustomResolver('r1', new InvalidEventResolver());

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'custom');

        $this->assertEventInfo($auditLog, array(
            'name' => 'custom',
            'description' => 'custom',
            'user' => 'By Command',
            'impersonatingUser' => null,
            'ip' => '',
        ));
    }

    public function testInvalidResolverWithDebugOn()
    {
        $this->event = new Basic();
        $this->initiateContainerWithDebugMode(true);

        $this->resolverFactory->setCommonResolver(new InvalidEventInfoResolver());

        $this->expectException(UnrecognizedEventInfoException::class);

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'basic');
        $this->assertNull($auditLog);
    }

    public function testInvalidCustomResolverWithDebugOn()
    {
        $this->event = new Basic();
        $this->resolverFactory = new EventResolverFactory(array('custom' => 'r1'), 'username', AuditLog::class);
        $this->resolverFactory->setTokenStorage($this->tokenStorage);
        $this->initiateContainerWithDebugMode(true);

        $this->resolverFactory->setCommonResolver(new DefaultEventResolver());
        $this->expectException(InvalidServiceException::class);
        $this->resolverFactory->addCustomResolver('r1', new InvalidEventResolver());
    }

    public function testAuditLogObjectEventResolver()
    {
        $this->event = new Basic();

        $this->resolverFactory->setCommonResolver(new AuditObjectResolver());

        $this->mockClientIpResolverForBrowserRequest();

        $this->mockSecurityAuthChecker();

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'basic');

        $this->assertEventInfo($auditLog, array(
            'name' => 'basic',
            'description' => 'basic',
            'user' => 'admin',
            'impersonatingUser' => null,
            'ip' => '127.0.0.1',
        ));
    }

    public function testEmbeddedEventResolver()
    {
        $this->event = new WithEmbeddedResolver();

        $this->mockClientIpResolverForBrowserRequest();

        $this->mockSecurityAuthChecker();

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'embedded');

        $this->assertEventInfo($auditLog, array(
            'name' => 'embedded',
            'description' => 'It is an embedded event',
            'user' => 'admin',
            'impersonatingUser' => null,
            'ip' => '127.0.0.1',
        ));
    }

    public function testNullEventInfo()
    {
        $this->event = new Basic();

        $this->resolverFactory->setCommonResolver(new NullResolver());

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'basic');
        $this->assertNull($auditLog);
    }

    public function testEntityEventResolver()
    {
        $this->event = new DoctrineEvent();

        $this->resolverFactory->setEntityEventResolver(new DefaultEventResolver());

        $this->mockClientIpResolverForBrowserRequest();
        $this->mockSecurityAuthChecker();

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'easy_audit.doctrine.object.created');

        $this->assertEventInfo($auditLog,
            array(
                'description' => 'easy_audit.doctrine.object.created',
                'name' => 'easy_audit.doctrine.object.created',
                'user' => 'admin',
                'impersonatingUser' => null,
                'ip' => '127.0.0.1',
            ));
    }

    public function testCustomValidEventResolver()
    {
        $this->event = new Basic();

        $this->resolverFactory->setCommonResolver(new CustomEventResolver());

        $this->mockClientIpResolverForBrowserRequest();

        $this->mockSecurityAuthChecker();

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'basic');

        $this->assertEventInfo($auditLog,
            array(
                'description' => 'Custom basic Description',
                'name' => 'basic',
                'user' => 'admin',
                'impersonatingUser' => null,
                'ip' => '127.0.0.1',
            ));
    }

    public function testCustomInValidEventResolverWithDebugOff()
    {
        $this->event = new Basic();

        $this->initiateContainerWithDebugMode(false);

        $this->resolverFactory->setCommonResolver(new InvalidEventResolver());
        $auditLog = $this->resolverFactory->getEventLog($this->event, 'basic');

        $this->assertNull($auditLog);
    }

    public function testCustomInValidEventResolverWithDebugOn()
    {
        $this->event = new Basic();

        $this->initiateContainerWithDebugMode(true);
        $this->expectException(InvalidServiceException::class);
        $this->resolverFactory->setCommonResolver(new InvalidEventResolver());
        $auditLog = $this->resolverFactory->getEventLog($this->event, 'basic');

        $this->assertNull($auditLog);
    }

    public function testEventTriggeredFromConsoleCommand()
    {
        $this->event = new Basic();

        $this->resolverFactory->setCommonResolver(new DefaultEventResolver());

        $this->resolverFactory->setRequestStack(null);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'basic');

        $this->assertEventInfo($auditLog,
            array(
                'description' => 'basic',
                'name' => 'basic',
                'user' => 'By Command',
                'impersonatingUser' => null,
                'type' => 'easy_audit.doctrine.object.created',
                'ip' => '',
            ));
    }

    public function testEventResolverWhenUserPropertyOptionIsNotGiven()
    {
        $this->event = new Basic();
        $this->resolverFactory = new EventResolverFactory(array(), null, AuditLog::class);
        $this->resolverFactory->setTokenStorage($this->tokenStorage);

        $this->resolverFactory->setCommonResolver(new DefaultEventResolver());

        $this->mockClientIpResolverForBrowserRequest();

        $this->mockSecurityAuthChecker();

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'basic');

        $this->assertEventInfo($auditLog, array(
            'name' => 'basic',
            'description' => 'basic',
            'user' => 'admin',
            'impersonatingUser' => null,
            'ip' => '127.0.0.1',
        ));
    }

    public function testShouldHandleCustomResolverList()
    {
        $this->event = new Basic();
        $this->resolverFactory = new EventResolverFactory(array('custom' => 'r1'), 'username', AuditLog::class);
        $this->resolverFactory->setTokenStorage($this->tokenStorage);

        $this->resolverFactory->setCommonResolver(new DefaultEventResolver());

        $this->resolverFactory->addCustomResolver('r1', new CustomEventResolver());

        $this->mockClientIpResolverForBrowserRequest();

        $this->mockSecurityAuthChecker();

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'custom');

        $this->assertEventInfo($auditLog, array(
            'name' => 'custom',
            'description' => 'Custom custom Description',
            'user' => 'admin',
            'impersonatingUser' => null,
            'ip' => '127.0.0.1',
        ));
    }

    public function testEventResolverWhenInvalidUserPropertyOptionIsGivenWithDebugOff()
    {
        $this->event = new Basic();

        $this->resolverFactory = new EventResolverFactory(array(), 'invalidProperty', AuditLog::class);
        $this->resolverFactory->setTokenStorage($this->tokenStorage);
        $this->initiateContainerWithDebugMode(false);

        $this->resolverFactory->setCommonResolver(new DefaultEventResolver());

        $this->mockClientIpResolverForBrowserRequest();

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));

        $this->mockSecurityAuthChecker();

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'basic');

        $this->assertEventInfo($auditLog, array(
            'name' => 'basic',
            'description' => 'basic',
            'user' => '',
            'impersonatingUser' => null,
            'ip' => '127.0.0.1',
        ));
    }

    public function testEventResolverWhenInvalidUserPropertyOptionIsGivenWithDebugOn()
    {
        $this->event = new Basic();

        $this->resolverFactory = new EventResolverFactory(array(), 'invalidProperty', AuditLog::class);
        $this->resolverFactory->setTokenStorage($this->tokenStorage);
        $this->initiateContainerWithDebugMode(true);

        $this->expectException(\Exception::class);
        $this->resolverFactory->setCommonResolver(new DefaultEventResolver());

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity()));

        $this->resolverFactory->getEventLog($this->event, 'basic');
    }

    public function testEventTriggeredByAnonymousUser()
    {
        $this->event = new Basic();
        $this->resolverFactory->setCommonResolver(new DefaultEventResolver());

        $this->mockClientIpResolverForBrowserRequest();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(new DummyToken(null));

        $auditLog = $this->resolverFactory->getEventLog($this->event, 'basic');

        $this->assertEventInfo($auditLog,
            array(
                'description' => 'basic',
                'name' => 'basic',
                'user' => 'Anonymous',
                'impersonatingUser' => null,
                'type' => 'easy_audit.doctrine.object.created',
                'ip' => '127.0.0.1',
            ));
    }

    public function testGetUserNameForAnonymousUser()
    {
        $this->mockClientIpResolverForBrowserRequest();
        $username = $this->resolverFactory->getUsername();
        $this->assertEquals('Anonymous', $username);
    }

    public function testCreateEventObjectFromArrayThrowsExceptionOnInvalidEntity()
    {
        $this->resolverFactory = new EventResolverFactory(array(), 'invalidProperty', Movie::class);
        $this->resolverFactory->setTokenStorage($this->tokenStorage);

        $this->event = new WithEmbeddedResolver();
        $this->initiateContainerWithDebugMode(true);
        $this->expectException('\Xiidea\EasyAuditBundle\Exception\UnrecognizedEntityException');

        $this->resolverFactory->getEventLog($this->event, 'embedded');
    }

    /**
     * @param bool $on
     * @param int  $callIndex
     */
    protected function initiateContainerWithDebugMode($on = true)
    {
        $this->resolverFactory->setDebug($on);
    }

    private function mockSecurityAuthChecker($isGranted = false)
    {
        $authChecker = $this->createMock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');

        $authChecker->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_PREVIOUS_ADMIN')
            ->willReturn($isGranted);

        $this->resolverFactory->setAuthChecker($authChecker);
    }

    private function mockClientIpResolverForBrowserRequest()
    {
        $request = $this->createMock('Symfony\Component\HttpFoundation\Request');

        $request->expects($this->any())
            ->method('getClientIp')
            ->willReturn('127.0.0.1');

        $requestStack = $this->createMock(RequestStack::class);
        $this->resolverFactory->setRequestStack($requestStack);
        $requestStack->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn($request);
    }

    /**
     * @param AuditLog $auditLog
     * @param array    $expected
     */
    private function assertEventInfo(AuditLog $auditLog, array $expected)
    {
        $this->assertNotNull($auditLog);
        $this->assertEquals($expected['description'], $auditLog->getDescription());
        $this->assertEquals($expected['name'], $auditLog->getType());
        $this->assertEquals($expected['name'], $auditLog->getTypeId());
        $this->assertEquals($expected['user'], $auditLog->getUser());
        $this->assertEquals($expected['impersonatingUser'], $auditLog->getImpersonatingUser());
        $this->assertEquals($expected['ip'], $auditLog->getIp());
        $this->assertNotNull($auditLog->getEventTime());
    }
}
