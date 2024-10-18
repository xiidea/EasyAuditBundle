<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Subscriber;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Subscriber\DoctrineSubscriber;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\DummyEntity;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie;
use Doctrine\Persistence\ObjectManager;

class DoctrineSubscriberTest extends TestCase
{
    /** @var MockObject */
    private $dispatcher;


    /** @var MockObject */
    private $entityManager;

    /** @var MockObject */
    private $metaData;

    public function setUp(): void
    {
        $this->dispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->entityManager = $this->createMock(ObjectManager::class);
        $this->metaData = $this->createMock(ClassMetadata::class);

        $this->entityManager->method('getClassMetadata')
            ->willReturn($this->metaData);
    }


    public function testCreateEventForAttributedEntity()
    {
        $subscriber = new DoctrineSubscriber(array());

        $this->invokeCreatedEventCall($subscriber);
    }

    public function testCreateEventForEntityNotConfiguredToTrack()
    {
        $subscriber = new DoctrineSubscriber(array());
        $this->invokeCreatedEventCall($subscriber, new DummyEntity());
    }

    public function testCreateEventForEntityConfiguredToTrack()
    {
        $subscriber = new DoctrineSubscriber(
            array('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie' => array('created'))
        );

        $this->invokeCreatedEventCall($subscriber);
    }

    public function testCreateEventForEntityConfiguredToTrackAllEvents()
    {
        $subscriber = new DoctrineSubscriber(array('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie' => array()));

        $this->invokeCreatedEventCall($subscriber);
    }

    public function testUpdateEventForEntityNotConfiguredToTrack()
    {
        $subscriber = new DoctrineSubscriber(array());
        $this->invokeUpdatedEventCall($subscriber, new DummyEntity());
    }

    public function testRemovedEventForEntityNotConfiguredToTrack()
    {
        $subscriber = new DoctrineSubscriber(array());
        $this->invokeDeletedEventCall($subscriber);
    }

    public function testRemovedEventForEntityConfiguredToTrackAllEvent()
    {
        $this->mockMetaData();
        $subscriber = new DoctrineSubscriber(array('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie' => array()));
        $this->invokeDeletedEventCall($subscriber);
    }


    /**
     * @param DoctrineSubscriber $subscriber
     */
    private function invokeCreatedEventCall($subscriber, $entity = null)
    {
        $subscriber->setDispatcher($this->dispatcher);
        $subscriber->postPersist(new LifecycleEventArgs($entity ?? new Movie(), $this->entityManager));
        $this->assertTrue(true);
    }

    /**
     * @param DoctrineSubscriber $subscriber
     */
    private function invokeUpdatedEventCall($subscriber, $entity = null)
    {
        $subscriber->setDispatcher($this->dispatcher);

        $subscriber->postUpdate(new LifecycleEventArgs($entity ?? new Movie(), $this->entityManager));
        $this->assertTrue(true);
    }

    /**
     * @param DoctrineSubscriber $subscriber
     */
    private function invokeDeletedEventCall($subscriber)
    {
        $subscriber->setDispatcher($this->dispatcher);
        $movie = new Movie();
        $subscriber->preRemove(new LifecycleEventArgs($movie, $this->entityManager));
        $subscriber->postRemove(new LifecycleEventArgs($movie, $this->entityManager));
        $this->assertTrue(true);
    }

    private function mockMetaData($data = ['id' => 1])
    {
        $this->metaData->method('getIdentifierValues')->willReturn($data);
    }
}
