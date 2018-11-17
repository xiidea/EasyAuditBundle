<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Xiidea\EasyAuditBundle\DependencyInjection\Compiler\LoggerFactoryPass;
use Xiidea\EasyAuditBundle\DependencyInjection\Compiler\MonologLoggerPass;
use Xiidea\EasyAuditBundle\DependencyInjection\Compiler\ResolverFactoryPass;
use Xiidea\EasyAuditBundle\DependencyInjection\Compiler\SubscriberPass;

class XiideaEasyAuditBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MonologLoggerPass());
        $container->addCompilerPass(new LoggerFactoryPass());
        $container->addCompilerPass(new SubscriberPass());
        $container->addCompilerPass(new ResolverFactoryPass());
    }
}
