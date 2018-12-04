Doctrine Entity Events
========================
You can track doctrine entity events. Currently Supported Events are [created, updated, deleted]. There are two way to achieve this.

### 1. By Configuration :

You can configure to select the entity you like to track as well as the events. If you like to track all events for your entity MyProject\Bundle\MyBundle\Entity\MyEntity just put `MyProject\Bundle\MyBundle\Entity\MyEntity : ~`

See the following example configuration value:

``` yaml
xiidea_easy_audit:
     doctrine_objects :                                              #Optional
          MyProject\Bundle\MyBundle\Entity\MyEntity : [updated, deleted]
          MyProject\Bundle\MyBundle\Entity\MyEntity2 : [deleted]
          MyProject\Bundle\MyBundle\Entity\MyEntity3 : ~
```

### 2. By Annotation

You can use annotation to tell XiideaEasyAuditBundle to track events of an entity.

@ORMSubscribedEvents: This annotation lets you define which event you like to track for a doctrine entity:

```php
//track only updated and created event
/**
 * @ORMSubscribedEvents(events = "updated, created")
 * or
 * @ORMSubscribedEvents("updated, created")
 */

//track all(updated, created, deleted) events
/**
 * @ORMSubscribedEvents(events = "updated, created, deleted")
 * or
 * @ORMSubscribedEvents("updated, created, deleted")
 * or even short form
 * @ORMSubscribedEvents()
 */
```

An entity example to track updated and created events

```php
<?php
// src/MyProject/MyBundle/Entity/MyEntity.php

use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * @ORM\Entity
 * @ORMSubscribedEvents(events = "updated, created")
 */
class MyEntity
{
    ...

}
```