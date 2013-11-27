Doctrine Entity Events
========================
You can track doctrine entity events. Currently Supported Events are [created, updated, deleted]. There are two way to achieve this.

### 1. By Configuration :

You can configure to select the entity you like to track as well as the events. If you like to track all events for your entity MyProject\Bundle\MyBundle\Entity\MyEntity just put `MyProject\Bundle\MyBundle\Entity\MyEntity : ~`

See the following example configuration value:

``` yaml
xiidea_easy_audit:
     doctrine_entities :                                              #Optional
          MyProject\Bundle\MyBundle\Entity\MyEntity : [updated, deleted]
          MyProject\Bundle\MyBundle\Entity\MyEntity2 : [deleted]
          MyProject\Bundle\MyBundle\Entity\MyEntity3 : ~
```

### 2. By implementing an interface

There is another way to tell XiideaEasyAuditBundle to track event of an entity. you need to implementing Xiidea\EasyAuditBundle\Doctrine\AuditAwareEntityInterface interface for the entity you wish to track.
alternately you could just extend Xiidea\EasyAuditBundle\Doctrine\AuditAwareEntityBase to track all event for your entity

to track created and updated event for MyEntity we can do:

```php
<?php
// src/MyProject/MyBundle/Entity/MyEntity.php

use Xiidea\EasyAuditBundle\Doctrine\AuditAwareEntityInterface;

class MyEntity implements AuditAwareEntityInterface
{
    ...

    public function getSubscribedEvents()
    {
        return ['created', 'updated'];
    }
}
```

or to track all supported events('created', 'updated', 'deleted') for MyEntity2 we can do :

```php
<?php
// src/MyProject/MyBundle/Entity/MyEntity2.php

use Xiidea\EasyAuditBundle\Doctrine\AuditAwareEntityBase;

class MyEntity2 extends AuditAwareEntityBase
{
    ....
}
```
