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

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\CommonDoctrineManager;
use Xiidea\EasyAuditBundle\Events\DoctrineObjectEvent;
use Xiidea\EasyAuditBundle\Resolver\DoctrineObjectEventResolver;
use Xiidea\EasyAuditBundle\Resolver\EntityEventResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\Basic;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\UserEntity;

class DoctrineObjectEventResolverTest extends TestCase
{
    /** @var DoctrineObjectEventResolver */
    private $eventResolver;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $entityManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $doctrine;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $dispatcher;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $unitOfWork;

    /** @var DoctrineObjectEvent */
    private $event;

    public function setUp()
    {
        $this->doctrine = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->eventResolver = new DoctrineObjectEventResolver();
        $this->eventResolver->setDoctrine($this->doctrine);

        $this->mockMethodCallTree();
    }

    public function testIsAnInstanceOfEventResolverInterface()
    {
        $this->assertInstanceOf('Xiidea\EasyAuditBundle\Resolver\EventResolverInterface', $this->eventResolver);
    }

    public function testIgnoreEventOtherThenDoctrineEntityEvent()
    {
        $this->assertNull($this->eventResolver->getEventLogInfo(new Basic(), 'basic'));
    }

    public function testIgnoreUnchangedUpdateEvent()
    {
        $this->createEventObjectForEntity(new UserEntity());
        $this->defineUnitOfWorkWith('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\UnitOfWork');
        $this->assertNull($this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.object.updated'), 'Ignoring unchanged update event');
    }

    public function testHandleUpdateEventWithEntityChangeSet()
    {
        $this->createEventObjectForEntity(new UserEntity());

        $this->defineUnitOfWorkWith('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\UnitOfWork');

        $this->unitOfWork->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($this->equalTo($this->event->getLifecycleEventArgs()->getObject()))
            ->willReturn($this->equalTo(array(array('something'))));

        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.object.updated');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('updated', 'UserEntity', 'id', 1), $eventInfo);
    }

    public function testHandleUpdateEventWithDocumentChangeSet()
    {
        $this->createEventObjectForEntity(new UserEntity());

        $this->defineUnitOfWorkWith('Xiidea\EasyAuditBundle\Tests\Fixtures\ODM\UnitOfWork');

        $this->unitOfWork->expects($this->once())
            ->method('getDocumentChangeSet')
            ->with($this->equalTo($this->event->getLifecycleEventArgs()->getObject()))
            ->willReturn($this->equalTo(array(array('something'))));

        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.object.updated');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('updated', 'UserEntity', 'id', 1), $eventInfo);
    }

    public function testHandleUpdateEventWithUnknownUnitOfWork()
    {
        $this->createEventObjectForEntity(new UserEntity());
        $this->defineUnitOfWorkWith('Xiidea\EasyAuditBundle\Tests\Fixtures\Common\UnknownUnitOfWork');

        $this->unitOfWork->expects($this->never())
            ->method($this->matchesRegularExpression('/get.*ChangeSet/'));

        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.object.updated');

        $this->assertNull($eventInfo);
    }

    public function testHandleCreatedEvent()
    {
        $this->createEventObjectForEntity(new UserEntity());
        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.object.created');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('created', 'UserEntity', 'id', 1), $eventInfo);
    }

    public function testHandleDeletedEvent()
    {
        $this->createEventObjectForEntity(new UserEntity());
        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.object.deleted');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('deleted', 'UserEntity', 'id', 1), $eventInfo);
    }

    public function testGetSingleIdentity()
    {
        $this->createEventObjectForEntity(new UserEntity(), []);
        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.object.deleted');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('deleted', 'UserEntity', '', ''), $eventInfo);
    }

    protected function mockMethodCallTree()
    {
        $this->entityManager = $this
            ->getMockBuilder(CommonDoctrineManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dispatcher = $this
            ->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrine->expects($this->any())
            ->method('getManager')
            ->willReturn($this->entityManager);
    }

    private function defineUnitOfWorkWith($class)
    {
        $this->unitOfWork = $this
            ->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($this->unitOfWork);
    }

    private function getExpectedEventInfo($event, $entity, $property = '', $value = '')
    {
        return array(
            'description' => "$entity has been $event".sprintf(' with %s = "%s"', $property, $value),
            'type' => "$entity $event",
        );
    }

    /**
     * @param $entity
     * @param array $identity
     */
    private function createEventObjectForEntity($entity, $identity = ['id' => 1])
    {
        $this->event = new DoctrineObjectEvent(new LifecycleEventArgs($entity, $this->entityManager), $identity);
    }
}
