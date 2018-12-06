<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Annotation;

@trigger_error(sprintf('The "%s" annotation is deprecated since version 1.4.10. Use "%s" instead.', ORMSubscribedEvents::class, SubscribeDoctrineEvents::class), E_USER_DEPRECATED);

/**
 * Annotation for ORM Subscribed Event.
 *
 * @Annotation
 * @Target({"CLASS"})
 *
 * @author Roni Saha <roni@xiidea.net>
 *
 * @deprecated since version 1.4.10
 */
final class ORMSubscribedEvents extends SubscribeDoctrineEvents
{
}
