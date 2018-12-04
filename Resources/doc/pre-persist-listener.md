Pre-Persist Listener
========================

When using the default **doctrine logger**, you may want to add customized data to the AuditLog object. You can do it by defining a doctrine Pre-Persist listener service like bellow:

#### Note: This appraoch is sugested only if you are using the doctrine logger. Because the extra data you set here would not be available to other loggers. In that case overriding the resolver should be your choice.

### 1. Write Your Listener Class

``` php
<?php
//src/MyProject/MyBundle/Listener/AuditLogPrePersistListener.php

namespace  MyProject\MyBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;

class AuditLogPrePersistListener
{

    public function prePersist(LifecycleEventArgs $args)
        {
    	    /** @var \Xiidea\EasyAuditBundle\Model\BaseAuditLog $entity */
            $entity = $args->getEntity();
    
            if ($entity instanceof BaseAuditLog) {
                //Do your extra processing 
            }
        }
}

```

### 2. Define your Listener as service

``` yaml
services:
    xiidea.easy_audit.prepersist_listener:
          class: MyProject\MyBundle\Listener\AuditLogPrePersistListener
          tags:
              - { name: doctrine.event_listener, event: prePersist  }

```
