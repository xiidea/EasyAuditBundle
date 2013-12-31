Easy Audit
==========

A Symfony2 Bundle To Log Selective Events. It is easy to configure and easy to customize for your need.

Install
-------
1. Add EasyAuditBundle in your composer.json
2. Enable the Bundle
3. Create audit_log entity class
4. Configure config.yml
5. Update Database Schema

### 1. Add EasyAuditBundle in your composer.json

Add EasyAuditBundle in your composer.json:

```js
{
    "require": {
        "xiidea/easy-audit": "1.2.*@dev"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update xiidea/easy-audit
```

Composer will install the bundle to your project's `vendor/xiidea` directory.

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

### 3. Create audit_log entity class

The XiideaEasyAuditBundle supports Doctrine ORM by default. However, you must provide a concrete AuditLog class. Follow the [instructions](./docs/audit-log-entity-orm.md) to set up the class:


### 4. Configure config.yml

You can find sample config data in `config/config.yml` file

``` yaml
# app/config/config.yml
xiidea_easy_audit:
    #resolver: xiidea.easy_audit.default_event_resolver        #Optional
    entity_class : MyProject\MyBundle\Entity\AuditLog          #Required

    #user property to use as actor of an event
    #valid value will be any valid property of your user class
    user_property : ~ # or username                            #Optional

    #List of doctrine entity:event you wish to track
    # valid events are = [created, updated, deleted]
    #doctrine_entities :                                              #Optional
    #     MyProject\Bundle\MyBundle\Entity\MyEntity : [created, updated, deleted]
    #     MyProject\Bundle\MyBundle\Entity\MyEntity2 : ~

    #List all events you want to track  required
    events :                                                   #Required
        - security.interactive_login

    #List all custom resolver for event
    #custom_resolvers :                                        #Optional
          #docudex.document.created : custom.event_resolver
```

### 5. Update Database Schema

As all setup done, now you need to update your database schema. To do so,run the following command from your project directory
``` bash
$ php app/console doctrine:schema:update --force
```

### Cookbook

Look the cookbook for another interesting things.

- [Embed Resolver with event](docs/embed-resolver.md)
- [Override Resolver](docs/override-resolver.md)
- [Custom Logger](docs/custom-logger.md)
- [Custom Resolver](docs/custom-resolver.md)
- [Doctrine Entity Event](docs/doctrine-entity-events.md)
