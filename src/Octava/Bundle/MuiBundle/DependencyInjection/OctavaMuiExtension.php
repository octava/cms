<?php

namespace Octava\Bundle\MuiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see
 * {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OctavaMuiExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $pagesDefinition = $container->getDefinition('octava_mui.event_listener.office_by_locale_listener');
        $pagesDefinition->addArgument($config['url_ignore_prefixes']);

        $definition = new Definition(
            $container->getParameter('octava_mui.dict.currencies.class'),
            [$config['currencies'],]
        );
        $container->setDefinition('octava_mui.dict.currencies', $definition);

        $definition = new Definition(
            $container->getParameter('octava_mui.config.route_config.class'),
            [$config,]
        );
        $container->setDefinition('octava_mui.config.route_config', $definition);

        $localeConfig = $container->getDefinition('octava_mui.config.admin_locales_config');
        $localeConfig->addArgument($config['admin_locales']);
    }
}
