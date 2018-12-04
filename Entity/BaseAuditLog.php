<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Entity;

use Xiidea\EasyAuditBundle\Model\BaseAuditLog as BaseClass;

@trigger_error(sprintf('The "%s" is deprecated since version 1.4.10. Use "%s" instead.', BaseAuditLog::class, BaseClass::class), E_USER_DEPRECATED);

/**
 * Class BaseAuditLog
 * @package Xiidea\EasyAuditBundle\Entity
 *
 * @deprecated since 1.4.10
 */
class BaseAuditLog extends BaseClass
{
}
