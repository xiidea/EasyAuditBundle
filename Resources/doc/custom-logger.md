# Custom Logger

You could easily create your own logger suitable to your needs. The Change Required to use your Logger is very simple. Just follow the steps:

### 1. Write Your LoggerClass

```php
<?php
//src/Logger/CustomLogger.php

namespace  App\Logger;

use Xiidea\EasyAuditBundle\Logger\LoggerInterface;
use Xiidea\EasyAuditBundle\Model\BaseAuditLog as AuditLog;

class CustomLogger implements LoggerInterface
{

    public function log(AuditLog $event)
    {
        //...
    }
}
```

### 2. Define your Logger as service

```yaml
services:
     class: App\Logger\CustomLogger
     tags:
          - { name: easy_audit.logger }
```
