Embed Resolver with event
==========================

Sometime it is easy if you could embed your resolver with your event itself. Easy audit also support such implementation. What you need to do just write your event class implementing `Xiidea\EasyAuditBundle\Resolver\EventResolverInterface`

``` php
<?php

use Symfony\Component\DependencyInjection\ContainerInterface

class MyEvent extends Event implements EventResolverInterface
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

    public function getEventLogInfo()
    {
        return array(
            'description'=>'Embeded Event description',
            'type'=>$this->getname()
        );
    }

}

```