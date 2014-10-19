Define events with subscriber
==============================

You can now expose/define your loggable events from your bundle using event subscriber instead of defining in configuration file. What you need to do, define a easy_audit.event_subscriber service implementing `Xiidea\EasyAuditBundle\Subscriber\EasyAuditEventSubscriberInterface`

### 1. Write Your AuditLogEventSubscriber class

``` php
<?php
src/MyProject/MyBundle/Subscriber/MyAuditLogEventSubscriber.php

namespace MyProject\MyBundle\Subscriber;

use Xiidea\EasyAuditBundle\Subscriber\EasyAuditEventSubscriberInterface

class MyAuditLogEventSubscriber implements EasyAuditEventSubscriberInterface
{
   public function getSubscribedEvents()
       {
           return array(
               "some_resolver" => "some_event",
               "some_other_resolver" => array(
                   "event_for_other_resolver_1",
                   "event_for_other_resolver_2"
               ),
               "event_for_default_resolver_1",
               "event_for_default_resolver_2",
               "event_for_default_resolver_3"
           );
       }

}

```

### 2. Define Subscriber as service

``` yaml
services:
     class: MyProject\MyBundle\Subscriber\MyAuditLogEventSubscriber
     tags:
         - { name: easy_audit.event_subscriber }

```

If you want you can optionally define the resolver for the subscribed events like:
 
``` yaml
services:
     class: MyProject\MyBundle\Subscriber\MyAuditLogEventSubscriber
     tags:
         - { name: easy_audit.event_subscriber, resolver : your_resolver_service_id }

```
