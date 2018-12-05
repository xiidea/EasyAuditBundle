<?php
/**
 * Created by IntelliJ IDEA.
 * User: roni
 * Date: 11/6/14
 * Time: 5:00 PM
 */

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Common;


use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class DummyLifecycleEventArgs extends LifecycleEventArgs
{
    public function __construct($entity, $manager = null)
    {
        parent::__construct($entity, $manager);

    }
}