<?php

/*
 * This file is part of the EasyAudit package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Repository;

use Doctrine\ORM\EntityRepository;

class AuditLogRepository extends EntityRepository
{
    public function create($data = array())
    {
        $entityClass = $this->getEntityName();
        $entity      = new $entityClass();

        if (!empty($data)) {
            $entity->fromArray($data);
        }

        return $entity;
    }
}