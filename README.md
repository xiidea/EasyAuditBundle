Easy Audit
==========

A Symfony2 Bundle To Log Selective Events. It is easy to configure and easy to customize for your need.

Install
-------
1. Add EasyAuditBundle in your composer.json
2. Enable the Bundle
3. Configure config.yml

### 1. Add EasyAuditBundle in your composer.json

Add this bundle to your `vendor/` dir:

```json
{
    "require": {
        "xiidea/easy-audit": "1.0.*@dev"
    }
}
```

### 2. Enable the Bundle

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Xiidea\EasyAuditBundle\XiideaEasyAuditBundle(),
    );
}
```

### 3. Configure config.yml

You can find sample config data in `config/config.yml` file

``` yaml
# app/config/config.yml
xiidea_easy_audit:
    #resolver: xiidea.easy_audit.default_event_resolver        #Optional
    #logger: xiidea.easy_audit.logger.service                  #Optional
    #log_event_class : Xiidea\EasyAuditBundle\Event\LogEvent   #Optional

    #List all events you want to track  required
    events :                                                   #Required
        - security.interactive_login

    #List all custom resolver for event
    #custom_resolvers :                                        #Optional
          #docudex.document.created : custom.event_resolver
```

### Cookbook

Look the cookbook for another interesting things.

- [Embed Resolver with event](docs/embed-resolver.md)
- [Override Resolver](docs/override-resolver.md)
- [Override Logger](docs/override-logger.md)
- [Custom Resolver](docs/custom-resolver.md)
