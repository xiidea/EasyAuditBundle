Custom Resolver For Specific Event
==================================
You could easily customize CustomEventResolver with a version adapted to your needs. The Change Required to use your Resolver is very simple. Just follow the steps:

### 1. Write Your ResolverClass

``` php
<?php

namespace Xiidea\EasyAuditBundle\Resolver;

use Symfony\Component\DependencyInjection\ContainerInterface

class CustomEventResolver implements EventResolverInterface
{

    private $container;

    public function __construct(ContainerInterface $container) {

        $this->container = $container;
    }

    public function getEventLogInfo($event = null)
    {
        return array(
            'description'=>'Custom description',
            'type'=>$event->getname(),
            'user'=>$this->getUsername(),
        );
    }

    public function getUser()
    {
        if (!$this->container->has('security.context')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->container->get('security.context')->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    public function getUsername()
    {
        $user = $this->getUser();

        if($user == null){
            return 'Anonymous';
        }

        return $user->getUsername();
    }
}

```

### 2. Define your resolver as service

``` yaml
services:
    custom.event_resolver.service:
         class: Xiidea\EasyAuditBundle\Resolver\CustomEventResolver
         arguments: [@service_container]

```

### 3. Use this resolver for specific event(s)

You can now use this resolver for specific event by setting following configuration

``` yaml
xiidea_easy_audit:
    custom_resolvers :
           security.interactive_login : custom.event_resolver.service
           fos_user.security.implicit_login : custom.event_resolver.service


```