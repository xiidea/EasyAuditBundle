Embed Resolver with event
==========================

Sometime it is easy if you could embed your resolver with your event itself. Easy audit also support such implementation. What you need to do just write your event class implementing `Xiidea\EasyAuditBundle\Resolver\EmbeddedEventResolverInterface`

``` php
<?php

use Xiidea\EasyAuditBundle\Resolver\EmbeddedEventResolverInterface;

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

    public function getEventLogInfo($eventName, EventDispatcherInterface $dispatcher)
    {
        return array(
            'description'=>'Embeded Event description',
            'type'=>$eventName
        );
    }

}

```