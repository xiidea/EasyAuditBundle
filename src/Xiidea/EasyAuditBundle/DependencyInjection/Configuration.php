<?php

/*
 * This file is part of the EasyAudit package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('xiidea_easy_audit');

        $rootNode
            ->children()
                ->scalarNode('resolver')->defaultValue('xiidea.easy_audit.default_event_resolver')->end()
                ->scalarNode('logger')->defaultValue('xiidea.easy_audit.logger.service')->end()
                ->scalarNode('entity_class')->end()
                ->variableNode('events')->defaultValue(array())->end()
                ->variableNode('custom_resolvers')->defaultValue(array())->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
