Custom Logger
========================

You could easily create your own logger suitable to your needs. The Change Required to use your Logger is very simple. Just follow the steps:

### 1. Write Your LoggerClass

``` php
<?php
src/MyProject/MyBundle/Resolver/CustomLogger.php

namespace  MyProject\MyBundle\Logger;

use Symfony\Component\DependencyInjection\ContainerInterface

class CustomLogger implements LoggerInterface
{

    public function log(AuditLog $event)
    {
        //...
    }
}

```

### 2. Define your Logger as service

``` yaml
services:
     class: MyProject\MyBundle\Logger\CustomLogger
     tags:
          - { name: easy_audit.logger }

```
