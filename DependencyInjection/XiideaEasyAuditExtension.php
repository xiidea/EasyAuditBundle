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
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class XiideaEasyAuditExtension extends Extension
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

        if (!$container->hasAlias('doctrine_mongodb')) {
            $config['document_event_resolver'] = 'none';
        }

        $this->loadDefaultResolverServices($config, $loader);

        if ($config['doctrine_documents'] !== false) {
            $loader->load('doctrine_services.yml');
        }
    }

    /**
     * @param $config
     * @param $loader
     */
    protected function loadDefaultResolverServices($config, LoaderInterface $loader)
    {
        if ($config['resolver'] === 'xiidea.easy_audit.default_event_resolver') {
            $loader->load('default/event-resolver.yml');
        }

        if (true === $config['default_logger']) {
            $loader->load('default/logger.yml');
        }

        if ($config['document_event_resolver'] === 'xiidea.easy_audit.default_document_event_resolver') {
            $loader->load('default/document-event-resolver.yml');
        }
    }
}
