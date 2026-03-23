# Embed Resolver with event

Sometimes it is easier to embed the resolver logic directly in your event class. EasyAuditBundle supports this via `EmbeddedEventResolverInterface`. Two approaches are available depending on how you prefer to register and dispatch your events.

## Write your event class

```php
<?php

use Xiidea\EasyAuditBundle\Resolver\EmbeddedEventResolverInterface;
use Symfony\Contracts\EventDispatcher\Event;

class MyEvent extends Event implements EmbeddedEventResolverInterface
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }


    public function getData()
    {
        return $this->data;
    }

    public function getEventLogInfo($eventName)
    {
        return array(
            'description'=>'Embeded Event description',
            'type'=>$eventName
        );
    }
}
```

---

## Approach 1: Interface-Based Dispatch Shortcut

Dispatch using the interface FQCN as the event name. No subscriber or extra configuration is needed.

```php
use Xiidea\EasyAuditBundle\Resolver\EmbeddedEventResolverInterface;

$dispatcher->dispatch(new MyEvent($data), EmbeddedEventResolverInterface::class);
```

The audit log `type` will be set to the concrete class name (`MyEvent`), not the interface name.

---

## Approach 2: Subscriber-Based Registration

If you prefer Symfony's more typical class-based event registration, add `MyEvent::class` to your subscriber. See [subscriber.md](subscriber.md) for details.

```php
$dispatcher->dispatch(new MyEvent($data));
```
