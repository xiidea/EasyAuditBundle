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


use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\DummyLifecycleEventArgs as LifecycleEventArgs;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;
use Xiidea\EasyAuditBundle\Subscriber\DoctrineSubscriber;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie;

class DoctrineSubscriberTest extends TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject  */
    private $container;

    public function setUp()
    {
        $this->container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
    }

    public function testInstanceOnSubscriber()
    {
        $this->assertInstanceOf('Doctrine\Common\EventSubscriber', new DoctrineSubscriber());
    }

    public function testConstructor()
    {
        $entities = array('entity1', 'entity2');
        $subscriber = new DoctrineSubscriber($entities);
        $this->assertAttributeEquals($entities, 'entities', $subscriber);
        $subscriber = new DoctrineSubscriber(array());
        $this->assertAttributeEquals(array(), 'entities', $subscriber);
    }

    public function testSubscribedEvents()
    {
        $subscriber = new DoctrineSubscriber();
        $this->assertEquals(array(
            'postPersist',
            'postUpdate',
            'preRemove',
        ),$subscriber->getSubscribedEvents());
    }

    public function testCreateEventForAnnotatedEntity()
    {
        $annotation = new ORMSubscribedEvents(array('events'=>'created'));

        $this->initializeAnnotationReader($annotation);
        $this->initializeDispatcher();

        $subscriber = new DoctrineSubscriber(array());

        $this->invokeCreatedEventCall($subscriber);

    }

    public function testCreateEventForEntityNotConfiguredToTrack()
    {
        $this->initializeAnnotationReader(null);
        $subscriber = new DoctrineSubscriber(array());
        $this->invokeCreatedEventCall($subscriber);
    }

    public function testCreateEventForEntityConfiguredToTrack()
    {
        $this->initializeAnnotationReader();
        $this->initializeDispatcher();
        $subscriber = new DoctrineSubscriber(array('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie'=>array('created')));

        $this->invokeCreatedEventCall($subscriber);
    }

    public function testCreateEventForEntityConfiguredToTrackAllEvents()
    {
        $this->initializeAnnotationReader();
        $this->initializeDispatcher();
        $subscriber = new DoctrineSubscriber(array('Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie'=>array()));

        $this->invokeCreatedEventCall($subscriber);
    }

    public function testUpdateEventForEntityNotConfiguredToTrack()
    {
        $this->initializeAnnotationReader(null);
        $subscriber = new DoctrineSubscriber(array());
        $this->invokeUpdatedEventCall($subscriber);
    }

    public function testRemovedEventForEntityNotConfiguredToTrack()
    {
        $this->initializeAnnotationReader(null);
        $subscriber = new DoctrineSubscriber(array());
        $this->invokeDeletedEventCall($subscriber);
    }

    private function initializeAnnotationReader($metaData = null)
    {
        $annotationReader = $this->getMockBuilder('\Doctrine\Common\Annotations\FileCacheReader')
            ->disableOriginalConstructor()
            ->getMock();

        $annotationReader->expects($this->once())
            ->method('getClassAnnotation')
            ->willReturn($metaData);

        $this->container->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('annotation_reader'))
            ->willReturn($annotationReader);
    }

    private function initializeDispatcher()
    {
        $dispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->container->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('event_dispatcher'))
            ->willReturn($dispatcher);
    }

    /**
     * @param DoctrineSubscriber $subscriber
     */
    private function invokeCreatedEventCall($subscriber)
    {
        $subscriber->setContainer($this->container);

        $subscriber->postPersist(new LifecycleEventArgs(new Movie()));
    }

    /**
     * @param DoctrineSubscriber $subscriber
     */
    private function invokeUpdatedEventCall($subscriber)
    {
        $subscriber->setContainer($this->container);

        $subscriber->postUpdate(new LifecycleEventArgs(new Movie()));
    }

    /**
     * @param DoctrineSubscriber $subscriber
     */
    private function invokeDeletedEventCall($subscriber)
    {
        $subscriber->setContainer($this->container);

        $subscriber->preRemove(new LifecycleEventArgs(new Movie()));
    }
}
 