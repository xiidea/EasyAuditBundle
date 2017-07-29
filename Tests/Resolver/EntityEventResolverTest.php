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

    /** @var  \PHPUnit_Framework_MockObject_MockObject  */
    private $container;

    /** @var  \PHPUnit_Framework_MockObject_MockObject  */
    private $kernel;

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
        $this->container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->kernel = $this->createMock('Symfony\Component\HttpKernel\KernelInterface');
        $this->eventResolver =  new EntityEventResolver();
        $this->eventResolver->setContainer($this->container);

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

    public function testPropertyNameAutoDetect()
    {
        $this->createEventObjectForEntity(new Movie(1, 'Car2'));
        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.entity.created');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('created', 'Movie', "name", "Car2"), $eventInfo);
    }

    public function testPropertyNameAutoDetectFallback()
    {
        $this->createEventObjectForEntity(new DummyEntity());
        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.entity.created');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('created', 'DummyEntity'), $eventInfo);
    }

    public function testPropertyNameAutoDetectedButInaccessible()
    {
        $this->createEventObjectForEntity(new EntityWithoutGetMethod());
        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.entity.created');

        $this->assertNotNull($eventInfo);
        $expectedEventInfo = $this->getExpectedEventInfo('created', 'EntityWithoutGetMethod', 'title', "");

        $this->assertEquals($expectedEventInfo['type'], $eventInfo['type']);
        $this->assertTrue(strpos($eventInfo['description'],'{INACCESSIBLE} property') !== false);
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
        $this->doctrine = $this
            ->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->container->expects($this->any())
            ->method('get')
            ->with($this->equalTo('doctrine'))
            ->willReturn($this->doctrine);

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
            'description'=> "$entity has been $event" . $this->getExpectedEventDescription($property, $value),
            'type'=> "$entity $event",
        );
    }

    private function getExpectedEventDescription($property = "", $value = "")
    {

        if (empty($property)) {
            return "";
        }

        return sprintf(' with %s = "%s"', $property, $value);
    }

    /**
     * @param $entity
     */
    private function createEventObjectForEntity($entity)
    {
        $this->event = new DoctrineEntityEvent(new LifecycleEventArgs($entity, $this->entityManager));
    }

}
 