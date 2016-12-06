Logger channel
=========================
It is now possible to register logger for specific channel. channel is refers to log level. channel can be inclusive or exclusive. to define exclusive list add `!` sign before it.
Let assume you have two logger registered. you want `xiidea.easy_audit.logger.service` service to log only "info" and "debug" level events. Wheres rest of the events you wants to be logged by `file.logger` service.
Then you can configure the logger channel as bellow:

``` yaml

xiidea_easy_audit:

    logger_channel:
        xiidea.easy_audit.logger.service: ["info", "debug"]
        file.logger: ["!info", "!debug"]

```


##Notes:

 - If no channel configured for a logger service, it will log all event
 - You can define either inclusive or exclusive list but not both for a logger. `file.logger: ["!info", "debug"]` is an invalid configuration
