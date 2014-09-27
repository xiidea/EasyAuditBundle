Step 3:  Create audit_log entity Doctrine ORM mapping
======================================================
The ORM implementation does not provide a concrete Comment class for your use,
you must create one. This can be done by extending the abstract entities
provided by the bundle and creating the appropriate mappings.

For example:

``` php
<?php
// src/MyProject/MyBundle/Entity/AuditLog.php

namespace MyProject\MyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Xiidea\EasyAuditBundle\Entity\BaseAuditLog;

/**
 * @ORM\Entity
 * @ORM\Table(name="audit_log")
 */
class AuditLog extends BaseAuditLog
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Type Of Event(Internal Type ID)
     *
     * @var string
     * @ORM\Column(name="type_id", type="string", length=200, nullable=false)
     */
    protected $typeId;

    /**
     * Type Of Event
     *
     * @var string
     * @ORM\Column(name="type", type="string", length=200, nullable=true)
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * Time Of Event
     * @var \DateTime
     * @ORM\Column(name="event_time", type="datetime")
     */
    protected $eventTime;

    /**
     * @var string
     * @ORM\Column(name="user", type="string", length=255)
     */
    protected $user;

}
```

## Configure your application

``` yaml
# app/config/config.yml

xiidea_easy_audit:
    entity_class : MyProject\MyBundle\Entity\AuditLog

```
