# Doctrine Object Events

You can track doctrine object(ORM/MongoDB) events. Currently Supported Events are [created, updated, deleted]. There are two way to achieve this.

### 1. By Configuration :

You can configure to select the entity you like to track as well as the events. If you like to track all events for your entity App\\Entity\\MyEntity just put `App\Entity\MyEntity : ~`

See the following example configuration value:

```yaml
xiidea_easy_audit:
     doctrine_objects :                                              #Optional
          App\Entity\MyEntity : [updated, deleted]
          App\Entity\MyEntity2 : [deleted]
          App\Entity\MyEntity3 : ~
```

### 2. By Annotation

You can use annotation to tell XiideaEasyAuditBundle to track events of an entity.

@SubscribeDoctrineEvents: This annotation lets you define which event you like to track for a doctrine entity:

```php
<?php
//track only updated and created event
#[SubscribeDoctrineEvents(["updated", "created"])]
// or
#[SubscribeDoctrineEvents("updated, created")]

//track all(updated, created, deleted) events
#[SubscribeDoctrineEvents(["updated", "created", "deleted"])]
// or even short form
#[SubscribeDoctrineEvents]
class MyEntity
{
   // ...
}
```

An entity example to track updated and created events

```php
<?php
// src/Entity/MyEntity.php

use Xiidea\EasyAuditBundle\Attribute\SubscribeDoctrineEvents;

#[SubscribeDoctrineEvents(events: ["updated", "created"])]
class MyEntity
{
   // ...

}
```

```php
<?php
// src/Document/MyDocument.php

use Xiidea\EasyAuditBundle\Attribute\SubscribeDoctrineEvents;

#[SubscribeDoctrineEvents(events: ["updated", "created"])]
class MyDocument
{
    //...

}
```
