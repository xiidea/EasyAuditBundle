Easy Audit
==========
[![Build Status](https://travis-ci.org/xiidea/EasyAuditBundle.svg?branch=master)](https://travis-ci.org/xiidea/EasyAuditBundle)
[![Coverage Status](https://coveralls.io/repos/xiidea/EasyAuditBundle/badge.svg?branch=master&service=github)](https://coveralls.io/github/xiidea/EasyAuditBundle?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/xiidea/EasyAuditBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/xiidea/EasyAuditBundle/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/xiidea/easy-audit/v/stable.png)](https://packagist.org/packages/xiidea/easy-audit)
[![Total Downloads](https://poser.pugx.org/xiidea/easy-audit/downloads.png)](https://packagist.org/packages/xiidea/easy-audit)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/b8802bf0-af10-4343-a6c4-846d6b481978/mini.png)](https://insight.sensiolabs.com/projects/b8802bf0-af10-4343-a6c4-846d6b481978)
[![knpbundles.com](http://knpbundles.com/xiidea/EasyAuditBundle/badge-short)](http://knpbundles.com/xiidea/EasyAuditBundle)


A Symfony2 Bundle To Log Selective Events. It is easy to configure and easy to customize for your need.

**Note:** If you are using **Symfony** version **older then 2.4** you need to use [EasyAuditBundle 1.2.x](https://github.com/xiidea/EasyAuditBundle/tree/1.2.x)   
**Note:** If you are using **Symfony** version **older then 2.6** you need to use [EasyAuditBundle 1.3.x](https://github.com/xiidea/EasyAuditBundle/tree/1.3.x)

Install
-------
1. Add EasyAuditBundle in your composer.json
2. Enable the Bundle
3. Create audit_log entity class
4. Configure config.yml
5. Update Database Schema

### 1. Add EasyAuditBundle in your composer.json

Add EasyAuditBundle in your composer.json:

```json
{
    "require": {
        "xiidea/easy-audit": "^1.4"
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

The XiideaEasyAuditBundle supports Doctrine ORM by default. However, you must provide a concrete AuditLog class. Follow the [instructions](https://github.com/xiidea/EasyAuditBundle/blob/master/Resources/doc/audit-log-entity-orm.md) to set up the class:


### 4. Configure config.yml

You can find sample config data in `Resources/config/config-sample.yml` file

``` yaml
# app/config/config.yml
xiidea_easy_audit:
    #resolver: xiidea.easy_audit.default_event_resolver                           #Optional
    #audit_log_class : MyProject\Bundle\MyBundle\Entity\AuditLog                  #Required
    #doctrine_event_resolver : xiidea.easy_audit.default_entity_event_resolver    #Optional
    #default_logger : true                                                        #Optional
    
    #user property to use as actor of an event
    #valid value will be any valid property of your user class
    user_property : ~ # or username                            #Optional

    #List of doctrine entity:event you wish to track or set to false to disable logs for doctrine events
    # valid events are = [created, updated, deleted]
    #doctrine_objects :                                              #Optional
    #     MyProject\Bundle\MyBundle\Entity\MyEntity : [created, updated, deleted]
    #     MyProject\Bundle\MyBundle\Entity\MyEntity2 : []

    #List all events you want to track  (Optional from v1.2.1 you can now use subscriber to define it)
    events :                                                   #Optional
        - security.interactive_login

    #List all custom resolver for event
    #custom_resolvers :
    #       security.interactive_login : user.event_resolver
    #       security.authentication.failure : user.event_resolver

    #logger_channel:
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

Core Concepts
-------------

#### Logger:

`Logger` is the core service which are responsible for persist the event info. You can define as many logger as you like.
EasyAudit Bundled with a logger service `xiidea.easy_audit.logger.service` which is the default logger service. You can easily
disable the service by setting `default_logger: false` in configuration.

#### Resolver:
`Resolver` is like translator for an event. It used to translate an event to AuditLog entity. EasyAudit bundled with two(2)
resolver services `xiidea.easy_audit.default_event_resolver`, `xiidea.easy_audit.default_doctrine_event_resolver`. And a
custom EventResolver class `UserEventResolver` to illustrate how the transformation works. You can define as many resolver
service as you want and use them to handle different event. Here is the place you can set the severity level for a event. Default
level is `Psr\Log\LogLevel::INFO`. Custom severity levels are not available. EasyAudit supports the logging levels
described by [PSR-3](http://www.php-fig.org/psr/psr-3). These values are present for basic filtering purposes. You can
use this value as channel to register different logger to handle different event. If you add any other field to your
AuditLog object, this is the place to add those extra information (tags, metadata, etc..)

#### Channel
It is now possible to register logger for specific channel. channel is refers to log level. you can configure EasyAudit logger
services to handle only specific level of event.

Warning - BC Breaking Changes
-----------------------------

* Since v1.2.2 `pre_persist_listener` option has been removed. You can use [this cookbook](https://github.com/xiidea/EasyAuditBundle/blob/master/Resources/doc/pre-persist-listener.md) to achieve the same functionality
* Since v1.2.2 `EventResolverInterface` been split into `EmbeddedEventResolverInterface` and `EventResolverInterface`
* Since v1.3.x The new Event object has been adapted. And the signature of `EmbeddedEventResolverInterface` and 
  `EventResolverInterface` also changed. Now it expects extra $eventName parameter     


Cookbook
--------

Look the cookbook for another interesting things.

- [Embed Resolver with event](https://github.com/xiidea/EasyAuditBundle/blob/master/Resources/doc/embed-resolver.md)
- [Define events with subscriber](https://github.com/xiidea/EasyAuditBundle/blob/master/Resources/doc/subscriber.md)
- [Override Resolver](https://github.com/xiidea/EasyAuditBundle/blob/master/Resources/doc/override-resolver.md)
- [Custom Logger](https://github.com/xiidea/EasyAuditBundle/blob/master/Resources/doc/custom-logger.md)
- [Custom Resolver](https://github.com/xiidea/EasyAuditBundle/blob/master/Resources/doc/custom-resolver.md)
- [Doctrine Entity Event](https://github.com/xiidea/EasyAuditBundle/blob/master/Resources/doc/doctrine-entity-events.md)
- [Pre-Persist Listener](https://github.com/xiidea/EasyAuditBundle/blob/master/Resources/doc/pre-persist-listener.md)
- [Logger Channel](https://github.com/xiidea/EasyAuditBundle/blob/master/Resources/doc/logger-channel.md)
