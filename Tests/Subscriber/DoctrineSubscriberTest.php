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
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Subscriber\DoctrineSubscriber;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Book;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Machine;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie;

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
        $subscriber = new DoctrineSubscriber([]);

        $this->invokeCreatedEventCall($subscriber);
        $this->assertTrue(true);
    }

    public function testCreateEventForEntityNotConfiguredToTrack()
    {
        $subscriber = new DoctrineSubscriber([]);
        $this->invokeCreatedEventCall($subscriber);
        $this->assertTrue(true);
    }

    public function testCreateEventForEntityConfiguredToTrack()
    {
        $subscriber = new DoctrineSubscriber(['Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie' => ['created']]);

        $this->invokeCreatedEventCall($subscriber);
        $this->assertTrue(true);
    }

    public function testCreateEventForEntityConfiguredToTrackAllEvents()
    {
        $subscriber = new DoctrineSubscriber(['Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie' => []]);

        $this->invokeCreatedEventCall($subscriber);
        $this->assertTrue(true);
    }

    public function testUpdateEventForEntityNotConfiguredToTrack()
    {
        $subscriber = new DoctrineSubscriber([]);
        $this->invokeUpdatedEventCall($subscriber);
        $this->assertTrue(true);
    }

    public function testRemovedEventForEntityNotConfiguredToTrack()
    {
        $subscriber = new DoctrineSubscriber([]);
        $this->invokeDeletedEventCall($subscriber);
        $this->assertTrue(true);
    }

    public function testRemovedEventForEntityConfiguredToTrackAllEvent()
    {
        $this->mockMetaData();
        $subscriber = new DoctrineSubscriber(['Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie' => []]);
        $this->invokeDeletedEventCall($subscriber);
        $this->assertTrue(true);
    }


    /**
     * @param DoctrineSubscriber $subscriber
     */
    private function invokeCreatedEventCall($subscriber)
    {
        $subscriber->setDispatcher($this->dispatcher);

        $subscriber->postPersist(new LifecycleEventArgs(new Movie(), $this->entityManager));
        $subscriber->postPersist(new LifecycleEventArgs(new Machine(), $this->entityManager));
        $this->assertTrue(true);
    }

    /**
     * @param DoctrineSubscriber $subscriber
     */
    private function invokeUpdatedEventCall($subscriber)
    {
        $subscriber->setDispatcher($this->dispatcher);

        $subscriber->postUpdate(new LifecycleEventArgs(new Book(), $this->entityManager));
        $subscriber->postUpdate(new LifecycleEventArgs(new Machine(), $this->entityManager));
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
