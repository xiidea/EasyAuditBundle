<?php

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\ORM;


use Doctrine\Common\PropertyChangedListener;

class UnitOfWork implements PropertyChangedListener
{

    /**
     * Collect information about a property change.
     *
     * @param object $sender The object on which the property changed.
     * @param string $propertyName The name of the property that changed.
     * @param mixed $oldValue The old value of the property that changed.
     * @param mixed $newValue The new value of the property that changed.
     *
     * @return void
     */
    public function propertyChanged($sender, $propertyName, $oldValue, $newValue)
    {
    }

    public function getEntityChangeSet()
    {

    }
}