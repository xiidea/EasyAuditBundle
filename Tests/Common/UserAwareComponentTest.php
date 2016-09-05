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

use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\DummyToken;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\DummyUserAwareComponent;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\UserEntity;

class UserAwareComponentTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $container;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $securityContext;


    /** @var  DummyUserAwareComponent */
    private $userAwareComponent;

    public function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->userAwareComponent = new DummyUserAwareComponent();
        $this->userAwareComponent->setContainer($this->container);
    }

    /**
     * @expectedException \LogicException
     */
    public function testShouldThrowLogicExceptionWithoutSecurityBundle()
    {
        $this->container->expects($this->at(0))
            ->method('has')
            ->with($this->equalTo('security.token_storage'))
            ->willReturn(false);

        $user = $this->userAwareComponent->getUser();

        $this->assertNull($user);
    }

    public function testShouldReturnNullUserIfUserNotLoggedIn()
    {
        $this->initiateContainerWithSecurityContextCheck();

        $user = $this->userAwareComponent->getUser();

        $this->assertNull($user);
    }

    public function testShouldReturnUserObjectOnLoggedInState()
    {
        $this->initiateContainerWithSecurityContextCheck();

        $this->securityContext->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity(1, 'admin')));

        $user = $this->userAwareComponent->getUser();

        $this->assertNotNull($user);
        $this->assertEquals('admin', $user->getUsername());
        $this->assertEquals(1, $user->getId());
    }

    public function testShouldReturnNullIfAuthenticatedAnonymously()
    {
        $this->initiateContainerWithSecurityContextCheck();

        $this->securityContext->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(""));

        $user = $this->userAwareComponent->getUser();

        $this->assertNull($user);
    }

    public function testShouldReturnNullImpersonatingUserWhenSecurityTokenNotExists() {

        $this->initiateContainerWithOutSecurityContextCheck();

        $this->securityContext->expects($this->any())
            ->method('getToken')
            ->willReturn(null);

        $user = $this->userAwareComponent->getImpersonatingUserForTest();

        $this->assertNull($user);
    }

    public function testShouldReturnNullImpersonatingUserIfUserDoNotHavePreviousAdminRole() {
        $this->initiateContainerWithOutSecurityContextCheck();
        $this->mockSecurityAuthChecker(1);

        $this->securityContext->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity(1, 'a')));

        $user = $this->userAwareComponent->getImpersonatingUserForTest();

        $this->assertNull($user);
    }

    public function testShouldReturnImpersonatingUserIfUserHavePreviousAdminRole() {
        $this->initiateContainerWithOutSecurityContextCheck();
        $this->mockSecurityAuthChecker(1, true);

        $userToken = new DummyToken(new UserEntity(1, 'admin'));

        $this->securityContext->expects($this->any())
            ->method('getToken')
            ->willReturn(new DummyToken(new UserEntity(1, 'a', array(new SwitchUserRole('', $userToken)))));

        $user = $this->userAwareComponent->getImpersonatingUserForTest();

        $this->assertNotNull($user);
        $this->assertEquals('admin', $user->getUsername());
        $this->assertEquals(1, $user->getId());
    }

    private function mockSecurityAuthChecker($callIndex, $isGranted = false) {
        $authChecker = $this->getMock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');

        $this->container->expects($this->at($callIndex))
            ->method('get')
            ->with($this->equalTo('security.authorization_checker'))
            ->willReturn($authChecker);

        $authChecker->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_PREVIOUS_ADMIN')
            ->willReturn($isGranted);
    }


    protected function initiateContainerWithSecurityContextCheck($callIndex = 0)
    {
        $this->securityContext = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage')
            ->disableOriginalConstructor()
            ->getMock();

        $this->container->expects($this->at($callIndex))
            ->method('has')
            ->with($this->equalTo('security.token_storage'))
            ->willReturn(true);

        $this->container->expects($this->at($callIndex + 1))
            ->method('get')
            ->with($this->equalTo('security.token_storage'))
            ->willReturn($this->securityContext);
    }

    protected function initiateContainerWithOutSecurityContextCheck($callIndex = 0)
    {
        $this->securityContext = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage')
            ->disableOriginalConstructor()
            ->getMock();

        $this->container->expects($this->at($callIndex))
            ->method('get')
            ->with($this->equalTo('security.token_storage'))
            ->willReturn($this->securityContext);
    }
}
