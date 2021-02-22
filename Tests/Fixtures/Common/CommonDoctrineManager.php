<?php

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Common;

use Doctrine\Persistence\ObjectManager;

interface CommonDoctrineManager extends ObjectManager
{
    public function getUnitOfWork();
}
