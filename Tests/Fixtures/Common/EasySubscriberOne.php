<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Common;

use Xiidea\EasyAuditBundle\Subscriber\EasyAuditEventSubscriberInterface;

class EasySubscriberOne implements EasyAuditEventSubscriberInterface
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'custom_resolver1' => 'event1',
            'custom_resolver2' => [
                'custom_event1',
                'custom_event2'
            ],
            'common_event'
        ];
    }
}