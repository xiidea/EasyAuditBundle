Pre-Persist Listener
========================

When using the default doctrine logger, you may want to add customized data to the AuditLog object. You can do it by defining a doctrine Pre-Persist listener service like bellow:

### 1. Write Your Listener Class

``` php
<?php
src/MyProject/MyBundle/Listener/AuditLogPrePersistListener.php

namespace  MyProject\MyBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;

class AuditLogPrePersistListener
{

    public function prePersist(LifecycleEventArgs $args)
        {
    	    /** @var \Xiidea\EasyAuditBundle\Entity\BaseAuditLog $entity */
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
