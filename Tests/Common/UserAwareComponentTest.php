<?php

namespace Xiidea\EasyAuditBundle\Tests\Common;

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\DummyToken;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\DummyUserAwareComponent;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\UserEntity;

class UserAwareComponentTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $tokenStorage;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $authChecker;

    /** @var DummyUserAwareComponent */
    private $userAwareComponent;

    public function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authChecker = $this->createMock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $this->userAwareComponent = new DummyUserAwareComponent();
        $this->userAwareComponent->setTokenStorage($this->tokenStorage);
        $this->userAwareComponent->setAuthChecker($this->authChecker);
    }

    public function testShouldReturnNullUserIfUserNotLoggedIn()
    {
        $user = $this->userAwareComponent->getUser();

        $this->assertNull($user);
    }

    public function testShouldReturnUserObjectOnLoggedInState()
    {
        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity(1, 'admin')));

        $user = $this->userAwareComponent->getUser();

        $this->assertNotNull($user);
        $this->assertEquals('admin', $user->getUsername());
        $this->assertEquals(1, $user->getId());
    }

    public function testShouldReturnNullIfAuthenticatedAnonymously()
    {
        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(''));

        $user = $this->userAwareComponent->getUser();

        $this->assertNull($user);
    }

    public function testShouldReturnNullImpersonatingUserWhenSecurityTokenNotExists()
    {
        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(null);

        $user = $this->userAwareComponent->getImpersonatingUserForTest();

        $this->assertNull($user);
    }

    public function testShouldReturnNullImpersonatingUserIfUserDoNotHavePreviousAdminRole()
    {
        $this->mockSecurityAuthChecker();

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity(1, 'a')));

        $user = $this->userAwareComponent->getImpersonatingUserForTest();

        $this->assertNull($user);
    }

    public function testShouldReturnImpersonatingUserIfUserHavePreviousAdminRole()
    {
        $this->mockSecurityAuthChecker(true);

        $userToken = new DummyToken(new UserEntity(1, 'admin'));

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity(1, 'a', array(new SwitchUserRole('', $userToken)))));

        $user = $this->userAwareComponent->getImpersonatingUserForTest();

        $this->assertNotNull($user);
        $this->assertEquals('admin', $user->getUsername());
        $this->assertEquals(1, $user->getId());
    }

    private function mockSecurityAuthChecker($isGranted = false)
    {
        $this->authChecker->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_PREVIOUS_ADMIN')
            ->willReturn($isGranted);
    }
}
