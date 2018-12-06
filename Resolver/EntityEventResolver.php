<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Resolver;

@trigger_error(sprintf('The "%s" is deprecated since XiideaEasyAuditBundle 1.4.10. Use "%s" instead.', EntityEventResolver::class, DoctrineObjectEventResolver::class), E_USER_DEPRECATED);

/**
 * Class EntityEventResolver
 * @package Xiidea\EasyAuditBundle\Resolver
 *
 * @deprecated since 1.4.10
 */
class EntityEventResolver extends DoctrineObjectEventResolver
{
}
