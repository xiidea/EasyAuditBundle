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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\DummyToken;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\DummyUserAwareComponent;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\UserEntity;

class UserAwareComponentTest extends TestCase
{
    /** @var MockObject */
    private $tokenStorage;

    /** @var MockObject */
    private $authChecker;

    /** @var DummyUserAwareComponent */
    private $userAwareComponent;

    protected function setUp(): void
    {
        $this->authChecker = $this->createMock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $this->tokenStorage = new TokenStorage();
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
        $this->tokenStorage->setToken(new DummyToken(new UserEntity(1, 'admin')));

        $user = $this->userAwareComponent->getUser();

        $this->assertNotNull($user);
        $this->assertEquals('admin', $user->getUsername());
        $this->assertEquals(1, $user->getId());
    }

    public function testShouldReturnNullIfAuthenticatedAnonymously()
    {
        $this->tokenStorage->setToken(new DummyToken(''));

        $user = $this->userAwareComponent->getUser();

        $this->assertNull($user);
    }

    public function testShouldReturnNullImpersonatingUserWhenSecurityTokenNotExists()
    {
        $this->tokenStorage->setToken(null);

        $user = $this->userAwareComponent->getImpersonatingUserForTest();

        $this->assertNull($user);
    }

    public function testShouldReturnNullImpersonatingUserIfUserDoNotHavePreviousAdminRole()
    {
        $this->mockSecurityAuthChecker();

        $this->tokenStorage->setToken(new DummyToken(new UserEntity(1, 'a')));

        $user = $this->userAwareComponent->getImpersonatingUserForTest();

        $this->assertNull($user);
    }

    public function testShouldReturnImpersonatingUserIfUserHavePreviousAdminRole()
    {
        $this->mockSecurityAuthChecker(true);

        $userToken = new DummyToken(new UserEntity(1, 'admin'));

        $this->tokenStorage->setToken(new SwitchUserToken(new UserEntity(1, 'a'), '', 'main', [], $userToken));

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
