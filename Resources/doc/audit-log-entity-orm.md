Step 3:  Create audit_log entity mapping
======================================================
BaseAuditLog class does not provide ODM/ODM Mapping,
you must create one. This can be done by extending the BaseAuditLog model 
provided by the bundle and creating the appropriate mappings.

For example:

### Doctrine ORM Entity Class
 
``` php
<?php
// src/Entity/AuditLog.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Xiidea\EasyAuditBundle\Model\BaseAuditLog;

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
   
    /**
     * @var string
     * @ORM\Column(name="impersonatingUser", type="string", length=255, nullable=true)
     */
    protected $impersonatingUser;
    
    /**
     * @var string
     * @ORM\Column(name="ip", type="string", length=20, nullable=true)
     */
    protected $ip;    

}
```

##### Configure your application

``` yaml
# config/packages/xiidea_easy_audit.yaml

xiidea_easy_audit:
    audit_log_class : App\Entity\AuditLog

```

### Or, Doctrine ODM Document Class

``` php
<?php
// src/Document/AuditLog.php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Xiidea\EasyAuditBundle\Model\BaseAuditLog;

/**
 * @MongoDB\Document
 */
class AuditLog extends BaseAuditLog
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * Type Of Event(Internal Type ID)
     *
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $typeId;

    /**
     * Type Of Event
     *
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $type;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $description;

    /**
     * Time Of Event
     * @var \DateTime
     * @MongoDB\Field(type="date")
     */
    protected $eventTime;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $user;
   
    /**
     * @var string
     * @MongoDB\Field(type="string", nullable=true)
     */
    protected $impersonatingUser;
    
    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $ip;    

}
```

#### Configure your application

``` yaml
# config/packages/xiidea_easy_audit.yaml

xiidea_easy_audit:
    audit_log_class : App\Document\AuditLog

```