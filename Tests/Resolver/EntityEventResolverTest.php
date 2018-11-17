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


use Doctrine\ORM\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Events\DoctrineEntityEvent;
use Xiidea\EasyAuditBundle\Resolver\EntityEventResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\Basic;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\DummyEntity;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\EntityWithoutGetMethod;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\UserEntity;

class EntityEventResolverTest extends TestCase {

    /** @var  EntityEventResolver */
    private $eventResolver;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $entityManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $doctrine;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $dispatcher;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $unitOfWork;

    /** @var  DoctrineEntityEvent */
    private $event;

    public function setUp()
    {
        $this->doctrine = $this->createMock('Doctrine\Bundle\DoctrineBundle\Registry');
        $this->eventResolver =  new EntityEventResolver();
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

        $this->assertNull($this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.entity.updated'), "Ignoring unchanged update event");
    }

    public function testHandleUpdateEventWithChangeSet()
    {
        $this->createEventObjectForEntity(new UserEntity());

        $this->unitOfWork->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($this->equalTo($this->event->getLifecycleEventArgs()->getEntity()))
            ->willReturn($this->equalTo(array(array('something'))));

        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.entity.updated');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('updated', 'UserEntity', "id", 1), $eventInfo);
    }

    public function testHandleCreatedEvent()
    {
        $this->createEventObjectForEntity(new UserEntity());
        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.entity.created');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('created', 'UserEntity', "id", 1), $eventInfo);
    }

    public function testHandleDeletedEvent()
    {
        $this->createEventObjectForEntity(new UserEntity());
        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.entity.deleted');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('deleted', 'UserEntity', "id", 1), $eventInfo);
    }

    public function testGetSingleIdentity()
    {
        $this->createEventObjectForEntity(new UserEntity(), []);
        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.entity.deleted');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('deleted', 'UserEntity', "", ""), $eventInfo);

    }

    protected function mockMethodCallTree()
    {
        $this->entityManager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dispatcher = $this
            ->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrine->expects($this->any())
            ->method('getManager')
            ->willReturn($this->entityManager);

        $this->unitOfWork = $this
            ->getMockBuilder('\Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($this->unitOfWork);
    }

    private function getExpectedEventInfo($event, $entity, $property = "", $value = "")
    {
        return array(
            'description'=> "$entity has been $event" . sprintf(' with %s = "%s"', $property, $value),
            'type'=> "$entity $event",
        );
    }

    /**
     * @param $entity
     * @param array $identity
     */
    private function createEventObjectForEntity($entity, $identity = ['id' => 1])
    {
        $this->event = new DoctrineEntityEvent(new LifecycleEventArgs($entity, $this->entityManager), $identity);
    }

}
 