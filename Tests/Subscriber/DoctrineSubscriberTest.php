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
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\DummyEntity;
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


    public function testEntityConfigureToTrackForAttributedEntity()
    {
        $subscriber = new DoctrineSubscriber([]);

        $subscriber->setDispatcher($this->dispatcher);
        $subscriber->postPersist(new LifecycleEventArgs(new Movie(), $this->entityManager));
        $subscriber->postUpdate(new LifecycleEventArgs(new Movie(), $this->entityManager));
        $subscriber->preRemove(new LifecycleEventArgs(new Movie(), $this->entityManager));
        $subscriber->postRemove(new LifecycleEventArgs(new Movie(), $this->entityManager));
        $this->assertTrue(true);
    }

    public function testEntityNotConfiguredToTrack()
    {
        $subscriber = new DoctrineSubscriber([]);
        $subscriber->setDispatcher($this->dispatcher);
        $subscriber->postPersist(new LifecycleEventArgs(new DummyEntity(), $this->entityManager));
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
     * @return void
     */
    public function testCreatedEventTrackEntityViaYaml(): void
    {
        $subscriber = new DoctrineSubscriber([DummyEntity::class => ['created']]);
        $subscriber->setDispatcher($this->dispatcher);
        $dummyClass = new DummyEntity();
        $subscriber->preRemove(new LifecycleEventArgs($dummyClass, $this->entityManager));
        $subscriber->postRemove(new LifecycleEventArgs($dummyClass, $this->entityManager));
        $this->assertTrue(true);
    }


    public function testAnyEventToTrackConfigureViaYaml(): void
    {
        $subscriber = new DoctrineSubscriber([DummyEntity::class => []]);
        $subscriber->setDispatcher($this->dispatcher);
        $dummyClass = new DummyEntity();
        $subscriber->preRemove(new LifecycleEventArgs($dummyClass, $this->entityManager));
        $subscriber->postRemove(new LifecycleEventArgs($dummyClass, $this->entityManager));
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
    }

    private function mockMetaData($data = ['id' => 1])
    {
        $this->metaData->method('getIdentifierValues')->willReturn($data);
    }
}
