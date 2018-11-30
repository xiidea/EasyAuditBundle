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

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Events\DoctrineDocumentEvent;
use Xiidea\EasyAuditBundle\Resolver\DocumentEventResolver;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\Basic;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ODM\UserDocument;
use Xiidea\EasyAuditBundle\Resolver\EventResolverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DocumentEventResolverTest extends TestCase
{
    /** @var  DocumentEventResolver */
    private $eventResolver;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $documentManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $doctrine;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $dispatcher;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $unitOfWork;

    /** @var  DoctrineDocumentEvent */
    private $event;

    public function setUp()
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->eventResolver = new DocumentEventResolver();
        $this->eventResolver->setDoctrine($this->doctrine);

        $this->mockMethodCallTree();
    }

    public function testIsAnInstanceOfEventResolverInterface()
    {
        $this->assertInstanceOf(EventResolverInterface::class, $this->eventResolver);
    }

    public function testIgnoreEventOtherThenDoctrineDocumentEvent()
    {
        $this->assertNull($this->eventResolver->getEventLogInfo(new Basic(), 'basic'));
    }

    public function testIgnoreUnchangedUpdateEvent()
    {
        $this->createEventObjectForDocument(new UserDocument());

        $this->assertNull(
            $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.document.updated'),
            'Ignoring unchanged update event'
        );
    }

    public function testHandleUpdateEventWithChangeSet()
    {
        $this->createEventObjectForDocument(new UserDocument());

        $this->unitOfWork->expects($this->once())
            ->method('getDocumentChangeSet')
            ->with($this->equalTo($this->event->getLifecycleEventArgs()->getDocument()))
            ->willReturn($this->equalTo([['something']]));

        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.document.updated');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('updated', 'UserDocument', 'id', 1), $eventInfo);
    }

    public function testHandleCreatedEvent()
    {
        $this->createEventObjectForDocument(new UserDocument());
        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.document.created');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('created', 'UserDocument', 'id', 1), $eventInfo);
    }

    public function testHandleDeletedEvent()
    {
        $this->createEventObjectForDocument(new UserDocument());
        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.document.deleted');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('deleted', 'UserDocument', 'id', 1), $eventInfo);
    }

    public function testGetSingleIddocument()
    {
        $this->createEventObjectForDocument(new UserDocument(), []);
        $eventInfo = $this->eventResolver->getEventLogInfo($this->event, 'easy_audit.doctrine.document.deleted');

        $this->assertNotNull($eventInfo);
        $this->assertEquals($this->getExpectedEventInfo('deleted', 'UserDocument', '', ''), $eventInfo);
    }

    protected function mockMethodCallTree()
    {
        $this->documentManager = $this
            ->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dispatcher = $this
            ->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrine->expects($this->any())
            ->method('getManager')
            ->willReturn($this->documentManager);

        $this->unitOfWork = $this
            ->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->documentManager->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($this->unitOfWork);
    }

    private function getExpectedEventInfo($event, $document, $property = '', $value = '')
    {
        return [
            'description' => "$document has been $event" . sprintf(' with %s = "%s"', $property, $value),
            'type'        => "$document $event",
        ];
    }

    /**
     * @param $document
     * @param array $identity
     */
    private function createEventObjectForDocument($document, $identity = ['id' => 1])
    {
        $this->event = new DoctrineDocumentEvent(new LifecycleEventArgs($document, $this->documentManager), $identity);
    }
}
 