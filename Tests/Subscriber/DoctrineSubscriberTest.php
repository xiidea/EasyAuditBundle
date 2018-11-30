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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Annotation\ODMSubscribedEvents;
use Xiidea\EasyAuditBundle\Subscriber\DoctrineSubscriber;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ODM\Movie;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;

class DoctrineSubscriberTest extends TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $dispatcher;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $annotationReader;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $documentManager;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $metaData;

    public function setUp()
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->documentManager = $this->createMock(ObjectManager::class);
        $this->metaData = $this->createMock(ClassMetadataInfo::class);

        $this->documentManager->method('getClassMetadata')
            ->willReturn($this->metaData);

        $this->annotationReader = $this->getMockBuilder(FileCacheReader::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testInstanceOnSubscriber()
    {
        $this->assertInstanceOf(EventSubscriber::class, new DoctrineSubscriber());
    }

    public function testConstructor()
    {
        $documents = ['document1', 'document2'];
        $subscriber = new DoctrineSubscriber($documents);
        $this->assertAttributeEquals($documents, 'documents', $subscriber);
        $subscriber = new DoctrineSubscriber([]);
        $this->assertAttributeEquals([], 'documents', $subscriber);
    }

    public function testSubscribedEvents()
    {
        $subscriber = new DoctrineSubscriber();
        $this->assertEquals(
            [
                'postPersist',
                'postUpdate',
                'preRemove',
                'postRemove',
            ],
            $subscriber->getSubscribedEvents()
        );
    }

    public function testCreateEventForAnnotatedDocument()
    {
        $annotation = new ODMSubscribedEvents(['events' => 'created']);

        $this->initializeAnnotationReader($annotation);

        $subscriber = new DoctrineSubscriber([]);

        $this->invokeCreatedEventCall($subscriber);
    }

    public function testCreateEventForDocumentNotConfiguredToTrack()
    {
        $this->initializeAnnotationReader(null);
        $subscriber = new DoctrineSubscriber([]);
        $this->invokeCreatedEventCall($subscriber);
    }

    public function testCreateEventForDocumentConfiguredToTrack()
    {
        $this->initializeAnnotationReader();

        $subscriber = new DoctrineSubscriber([Movie::class => ['created']]);

        $this->invokeCreatedEventCall($subscriber);
    }

    public function testCreateEventForDocumentConfiguredToTrackAllEvents()
    {
        $this->initializeAnnotationReader();

        $subscriber = new DoctrineSubscriber([Movie::class => []]);

        $this->invokeCreatedEventCall($subscriber);
    }

    public function testUpdateEventForDocumentNotConfiguredToTrack()
    {
        $this->initializeAnnotationReader();
        $subscriber = new DoctrineSubscriber([]);
        $this->invokeUpdatedEventCall($subscriber);
    }

    public function testRemovedEventForDocumentNotConfiguredToTrack()
    {
        $this->initializeAnnotationReader(null);
        $subscriber = new DoctrineSubscriber([]);
        $this->invokeDeletedEventCall($subscriber);
    }

    public function testRemovedEventForDocumentConfiguredToTrackAllEvent()
    {
        $this->initializeAnnotationReader(null);

        $this->mockMetaData();
        $subscriber = new DoctrineSubscriber([Movie::class => []]);
        $this->invokeDeletedEventCall($subscriber);
    }

    private function initializeAnnotationReader($metaData = null)
    {
        $this->annotationReader->expects($this->once())
            ->method('getClassAnnotation')
            ->willReturn($metaData);
    }

    /**
     * @param DoctrineSubscriber $subscriber
     */
    private function invokeCreatedEventCall($subscriber)
    {
        $subscriber->setDispatcher($this->dispatcher);
        $subscriber->setAnnotationReader($this->annotationReader);

        $subscriber->postPersist(new LifecycleEventArgs(new Movie(), $this->documentManager));
    }

    /**
     * @param DoctrineSubscriber $subscriber
     */
    private function invokeUpdatedEventCall($subscriber)
    {
        $subscriber->setDispatcher($this->dispatcher);;
        $subscriber->setAnnotationReader($this->annotationReader);

        $subscriber->postUpdate(new LifecycleEventArgs(new Movie(), $this->documentManager));
    }

    /**
     * @param DoctrineSubscriber $subscriber
     */
    private function invokeDeletedEventCall($subscriber)
    {
        $subscriber->setDispatcher($this->dispatcher);;
        $subscriber->setAnnotationReader($this->annotationReader);

        $movie = new Movie();
        $subscriber->preRemove(new LifecycleEventArgs($movie, $this->documentManager));
        $subscriber->postRemove(new LifecycleEventArgs($movie, $this->documentManager));
    }

    private function mockMetaData($data = ['id' => 1])
    {
        $this->metaData->method('getIdentifierValues')->willReturn($data);
    }
}
 