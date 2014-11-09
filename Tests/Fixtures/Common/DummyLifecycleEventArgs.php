<?php
/**
 * Created by IntelliJ IDEA.
 * User: roni
 * Date: 11/6/14
 * Time: 5:00 PM
 */

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Common;


use Doctrine\ORM\Event\LifecycleEventArgs;

class DummyLifecycleEventArgs extends LifecycleEventArgs
{
    /**
     * @var object
     */
    private $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }
}