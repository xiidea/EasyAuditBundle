# Custom Resolver For Specific Event

You could easily customize CustomEventResolver with a version adapted to your needs. The Change Required to use your Resolver is very simple. Just follow the steps:

### 1. Write Your ResolverClass

```php
<?php
//src/App/Resolver/CustomEventResolver.php

namespace App\Resolver;

use Xiidea\EasyAuditBundle\Resolver\EventResolverInterface;
use Symfony\Contracts\EventDispatcher\Event;

class CustomEventResolver implements EventResolverInterface
{

    public function getEventLogInfo(Event $event, $eventName)
    {
        return array(
            'description'=>'Custom description',
            'type'=>$eventName
        );
    }

}
```

### 2. Define your resolver as service

```yaml
services:
    custom.event_resolver.service:
         class:  App\Resolver\CustomEventResolver
```

### 3. Use this resolver for specific event(s)

You can now use this resolver for specific event by setting following configuration

```yaml
xiidea_easy_audit:
    custom_resolvers :
           security.interactive_login : custom.event_resolver.service
           fos_user.security.implicit_login : custom.event_resolver.service
```
