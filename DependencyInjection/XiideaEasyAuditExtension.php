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
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class XiideaEasyAuditExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $key => $value) {
            $container->setParameter('xiidea.easy_audit.' . $key, $value);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->loadDefaultResolverServices($config, $loader);

        if (false !== $config['doctrine_objects']) {
            $loader->load('doctrine_services.yml');
        }
    }

    /**
     * @param $config
     * @param LoaderInterface $loader
     *
     * @throws \Exception
     */
    protected function loadDefaultResolverServices($config, LoaderInterface $loader)
    {
        if ('xiidea.easy_audit.default_event_resolver' === $config['resolver']) {
            $loader->load('default/event-resolver.yml');
        }

        if (true === $config['default_logger']) {
            $loader->load('default/logger.yml');
        }

        if (false !== $config['doctrine_objects'] && 'xiidea.easy_audit.default_doctrine_event_resolver' === $config['doctrine_event_resolver']) {
            $loader->load('default/doctrine-event-resolver.yml');
        }
    }

    /**
     * Allow an extension to prepend the extension configurations.
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container): void
    {
        $prependConfig = $this->getExtendedConfig($container);

        if (!empty($prependConfig)) {
            $container->prependExtensionConfig($this->getAlias(), $prependConfig);
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array
     */
    protected function getExtendedConfig(ContainerBuilder $container): array
    {
        $configs = array_merge(...$container->getExtensionConfig($this->getAlias()));

        $prependConfig = [];

        $doctrineConfig = $container->getExtensionConfig('doctrine');

        if (!empty($doctrineConfig) && !isset($configs['doctrine_event_resolver'])) {
            $prependConfig['doctrine_event_resolver'] = 'xiidea.easy_audit.default_doctrine_event_resolver';
        }

        return $prependConfig;
    }
}
