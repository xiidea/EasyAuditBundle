Easy Audit
==========
[![Build Status](https://travis-ci.org/xiidea/easy-audit.png?branch=master)](https://travis-ci.org/xiidea/easy-audit)
[![HHVM Status](http://hhvm.h4cc.de/badge/xiidea/easy-audit.svg)](http://hhvm.h4cc.de/package/xiidea/easy-audit)
[![Coverage Status](https://coveralls.io/repos/xiidea/easy-audit/badge.png?branch=master)](https://coveralls.io/r/xiidea/easy-audit?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/xiidea/easy-audit/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/xiidea/easy-audit/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/xiidea/easy-audit/v/stable.png)](https://packagist.org/packages/xiidea/easy-audit)
[![Total Downloads](https://poser.pugx.org/xiidea/easy-audit/downloads.png)](https://packagist.org/packages/xiidea/easy-audit)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/b8802bf0-af10-4343-a6c4-846d6b481978/mini.png)](https://insight.sensiolabs.com/projects/b8802bf0-af10-4343-a6c4-846d6b481978)
[![knpbundles.com](http://knpbundles.com/xiidea/easy-audit/badge-short)](http://knpbundles.com/xiidea/easy-audit)


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
        "xiidea/easy-audit": "1.3.*@dev"
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

The XiideaEasyAuditBundle supports Doctrine ORM by default. However, you must provide a concrete AuditLog class. Follow the [instructions](https://github.com/xiidea/easy-audit/blob/master/Resources/doc/audit-log-entity-orm.md) to set up the class:


### 4. Configure config.yml

You can find sample config data in `Resources/config/config-sample.yml` file

``` yaml
# app/config/config.yml
xiidea_easy_audit:
    #resolver: xiidea.easy_audit.default_event_resolver        #Optional
    entity_class : MyProject\MyBundle\Entity\AuditLog          #Required

    #user property to use as actor of an event
    #valid value will be any valid property of your user class
    user_property : ~ # or username                            #Optional

    #List of doctrine entity:event you wish to track or set to false to disable logs for doctrine events
    # valid events are = [created, updated, deleted]
    #doctrine_entities :                                              #Optional
    #     MyProject\Bundle\MyBundle\Entity\MyEntity : [created, updated, deleted]
    #     MyProject\Bundle\MyBundle\Entity\MyEntity2 : ~

    #List all events you want to track  (Optional from v1.2.1 you can now use subscriber to define it)
    events :                                                   #Optional
        - security.interactive_login

    #List all custom resolver for event
    #custom_resolvers :
    #       security.interactive_login : user.event_resolver
    #       security.authentication.failure : user.event_resolver

    #logger_chanel:
    #    xiidea.easy_audit.logger.service: ["info", "debug"]
    #    file.logger: ["!info", "!debug"]

    #Custom Event Resolver Service
services:
    #user.event_resolver:
    #     class: Xiidea\EasyAuditBundle\Resolver\UserEventResolver
    #     calls:
    #        - [ setContainer,[ @service_container ] ]
```

### 5. Update Database Schema

As all setup done, now you need to update your database schema. To do so,run the following command from your project directory
``` bash
$ php app/console doctrine:schema:update --force
```

### Warning - BC Breaking Changes ###

* Since v1.2.2 `pre_persist_listener` option has been removed. You can use [this cookbook](https://github.com/xiidea/easy-audit/blob/master/Resources/doc/pre-persist-listener.md) to achieve the same functionality 
* Since v1.2.2 `EventResolverInterface` been split into `EmbeddedEventResolverInterface` and `EventResolverInterface`
* Since v1.3.x The new Event object has been adapted. And the signature of `EmbeddedEventResolverInterface` and 
  `EventResolverInterface` also changed. Now it expects extra $eventName parameter     


### Cookbook

Look the cookbook for another interesting things.

- [Embed Resolver with event](https://github.com/xiidea/easy-audit/blob/master/Resources/doc/embed-resolver.md)
- [Define events with subscriber](https://github.com/xiidea/easy-audit/blob/master/Resources/doc/subscriber.md)
- [Override Resolver](https://github.com/xiidea/easy-audit/blob/master/Resources/doc/override-resolver.md)
- [Custom Logger](https://github.com/xiidea/easy-audit/blob/master/Resources/doc/custom-logger.md)
- [Custom Resolver](https://github.com/xiidea/easy-audit/blob/master/Resources/doc/custom-resolver.md)
- [Doctrine Entity Event](https://github.com/xiidea/easy-audit/blob/master/Resources/doc/doctrine-entity-events.md)
- [Pre-Persist Listener](https://github.com/xiidea/easy-audit/blob/master/Resources/doc/pre-persist-listener.md)
- [Logger Chanel](https://github.com/xiidea/easy-audit/blob/master/Resources/doc/logger-chanel.md)
