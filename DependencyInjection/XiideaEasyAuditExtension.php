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

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class XiideaEasyAuditExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $key => $value) {
            $container->setParameter('xiidea.easy_audit.' . $key, $value);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->loadDefaultResolverServices($config, $loader);

        if ($config['doctrine_entities'] !== false) {
            $loader->load('doctrine_services.yml');
        }
    }

    /**
     * @param $config
     * @param $loader
     */
    protected function loadDefaultResolverServices($config, LoaderInterface $loader)
    {
        if ($config['resolver'] == 'xiidea.easy_audit.default_event_resolver') {
            $loader->load('default/event-resolver.yml');
        }

        if (true === $config['default_logger']) {
            $loader->load('default/logger.yml');
        }

        if ($config['doctrine_entities'] !== false && $config['entity_event_resolver'] == 'xiidea.easy_audit.default_entity_event_resolver') {
            $loader->load('default/entity-event-resolver.yml');
        }
    }

    /**
     * Allow an extension to prepend the extension configurations.
     */
    public function prepend(ContainerBuilder $container)
    {
        $extensions = $container->getExtensionConfig('doctrine');
        $configs = $container->getExtensionConfig($this->getAlias());

        if (!empty($extensions) && !isset($configs['entity_event_resolver'])) {
            $container->prependExtensionConfig($this->getAlias(), ['entity_event_resolver' => 'xiidea.easy_audit.default_entity_event_resolver']);
        }
    }
}
