Step 3:  Create audit_log document Doctrine ODM mapping
======================================================
The ODM implementation does not provide a concrete AuditLog class for your use,
you must create one. This can be done by extending the abstract documents
provided by the bundle and creating the appropriate mappings.

For example:

``` php
<?php
// src/MyProject/MyBundle/Document/AuditLog.php

namespace MyProject\MyBundle\Document;

use use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Xiidea\EasyAuditBundle\Document\BaseAuditLog;

/**
 * @ODM\Document(collection="audit_log")
 */
class AuditLog extends BaseAuditLog
{
    /**
     * @ODM\Id(strategy="AUTO")
     */
    protected $id;

    /**
     * Type Of Event(Internal Type ID)
     *
     * @var string
     * @ODM\Field(name="type_id", type="string", nullable=false)
     */
    protected $typeId;

    /**
     * Type Of Event
     *
     * @var string
     * @ODM\Field(name="type", type="string", nullable=true)
     */
    protected $type;

    /**
     * @var string
     * @ODM\Field(name="description", type="string", nullable=true)
     */
    protected $description;

    /**
     * Time Of Event
     * @var \DateTime
     * @ODM\Field(name="event_time", type="datetime")
     */
    protected $eventTime;

    /**
     * @var string
     * @ODM\Field(name="user", type="string")
     */
    protected $user;
   
    /**
     * @var string
     * @ODM\Field(name="impersonatingUser", type="string", nullable=true)
     */
    protected $impersonatingUser;
    
    /**
     * @var string
     * @ODM\Field(name="ip", type="string", nullable=true)
     */
    protected $ip;    

}
```

## Configure your application

``` yaml
# app/config/config.yml

xiidea_easy_audit:
    document_class : MyProject\MyBundle\Document\AuditLog

```
