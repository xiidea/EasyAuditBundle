Doctrine Entity Events
========================
You can track doctrine entity events. Currently Supported Events are [created, updated, deleted]. You can configure to select the entity tou like to track as well as the events. If you like to track all events for your entity MyProject\Bundle\MyBundle\Entity\MyEntity just put `MyProject\Bundle\MyBundle\Entity\MyEntity : ~`

See the following example configuration value:

``` yaml
xiidea_easy_audit:
     doctrine_entities :                                              #Optional
          MyProject\Bundle\MyBundle\Entity\MyEntity : [updated, deleted]
          MyProject\Bundle\MyBundle\Entity\MyEntity2 : [deleted]
          MyProject\Bundle\MyBundle\Entity\MyEntity3 : ~
```
