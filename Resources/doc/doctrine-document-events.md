Doctrine Document Events
========================
You can track doctrine document events. Currently Supported Events are [created, updated, deleted]. There are two way to achieve this.

### 1. By Configuration :

You can configure to select the document you like to track as well as the events. If you like to track all events for your document MyProject\Bundle\MyBundle\Document\MyDocument just put `MyProject\Bundle\MyBundle\Document\MyDocument : ~`

See the following example configuration value:

``` yaml
xiidea_easy_audit:
     doctrine_documents :                                              #Optional
          MyProject\Bundle\MyBundle\Document\MyDocument : [updated, deleted]
          MyProject\Bundle\MyBundle\Document\MyDocument2 : [deleted]
          MyProject\Bundle\MyBundle\Document\MyDocument3 : ~
```

### 2. By Annotation

You can use annotation to tell XiideaEasyAuditBundle to track events of an document.

@ODMSubscribedEvents: This annotation lets you define which event you like to track for a doctrine document:

```php
//track only updated and created event
/**
 * @ODMSubscribedEvents(events = "updated, created")
 * or
 * @ODMSubscribedEvents("updated, created")
 */

//track all(updated, created, deleted) events
/**
 * @ODMSubscribedEvents(events = "updated, created, deleted")
 * or
 * @ODMSubscribedEvents("updated, created, deleted")
 * or even short form
 * @ODMSubscribedEvents()
 */
```

An document example to track updated and created events

```php
<?php
// src/MyProject/MyBundle/Document/MyDocument.php

use Xiidea\EasyAuditBundle\Annotation\ODMSubscribedEvents;

/**
 * @ODM\Document
 * @ODMSubscribedEvents(events = "updated, created")
 */
class MyDocument
{
    ...

}
```