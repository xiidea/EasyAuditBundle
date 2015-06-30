<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

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

        $this->addRequiredConfigs($rootNode);
        $this->addDefaultServices($rootNode);
        $this->addOptionalConfigs($rootNode);
        $this->addChannelHandlers($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addRequiredConfigs(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('user_property')->isRequired()->defaultValue(null)->end()
                ->scalarNode('entity_class')->cannotBeOverwritten()->isRequired()->cannotBeEmpty()->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addDefaultServices(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('resolver')->defaultValue('xiidea.easy_audit.default_event_resolver')->end()
                ->scalarNode('entity_event_resolver')
                    ->defaultValue('xiidea.easy_audit.default_entity_event_resolver')
                ->end()
                ->booleanNode('default_logger')->defaultValue(true)->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addOptionalConfigs(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->variableNode('doctrine_entities')->defaultValue(array())->end()
                ->variableNode('events')->defaultValue(array())->end()
                ->variableNode('custom_resolvers')->defaultValue(array())->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addChannelHandlers(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('loggerChannel')
            ->children()
                ->arrayNode('logger_channel')
                    ->canBeUnset()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->fixXmlConfig('channel', 'elements')
                            ->canBeUnset()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function($v) { return array('elements' => array($v)); })
                            ->end()
                            ->beforeNormalization()
                                ->ifTrue(function($v) { return is_array($v) && is_numeric(key($v)); })
                                ->then(function($v) { return array('elements' => $v); })
                            ->end()
                            ->validate()
                                ->ifTrue(function($v) { return empty($v); })
                                ->thenUnset()
                            ->end()
                            ->validate()->always($this->getChannelTypeValidator())->end()
                            ->children()
                                ->scalarNode('type')
                                    ->validate()
                                        ->ifNotInArray(array('inclusive', 'exclusive'))
                                        ->thenInvalid('The type of channels has to be inclusive or exclusive')
                                    ->end()
                                ->end()
                                ->arrayNode('elements')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @return \Closure
     */
    private function getChannelTypeValidator()
    {
        return function ($v) {
            $isExclusiveList = isset($v['type']) ? 'exclusive' === $v['type'] : null;
            $elements = array();

            foreach ($v['elements'] as $element) {
                Configuration::appendChannelTypes($element, $isExclusiveList, $elements);
            }

            return array('type' => $isExclusiveList ? 'exclusive' : 'inclusive', 'elements' => $elements);
        };
    }

    /**
     * @param boolean $invalid
     * @throws InvalidConfigurationException
     */
    public static function throwExceptionOnInvalid($invalid)
    {
        if(!$invalid) {
            return;
        }

        throw new InvalidConfigurationException(
            'Cannot combine exclusive/inclusive definitions in channels list'
        );
    }

    public static function appendChannelTypes($element, &$isExclusiveList, &$elements = array())
    {
        $isExclusiveItem = 0 === strpos($element, '!');

        self::throwExceptionOnInvalid(!$isExclusiveItem === $isExclusiveList);

        $elements[] = $isExclusiveItem ? substr($element, 1) : $element;
        $isExclusiveList = $isExclusiveItem;
    }
}
