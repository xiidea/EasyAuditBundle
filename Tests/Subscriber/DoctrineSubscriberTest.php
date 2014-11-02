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


use Xiidea\EasyAuditBundle\Subscriber\DoctrineSubscriber;

class DoctrineSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject  */
    private $container;

    public function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
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
}
 