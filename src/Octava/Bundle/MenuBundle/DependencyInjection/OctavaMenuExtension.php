<?php

namespace Octava\Bundle\MenuBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OctavaMenuExtension extends Extension
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

        $definition = new Definition(
            $container->getParameter('octava_menu.config.menu_config.class'),
            [$config]
        );
        $container->setDefinition('octava_menu.config.menu_config', $definition);

        $this->registerFormTypeTemplates($container);
    }

    protected function registerFormTypeTemplates(ContainerBuilder $container)
    {
        $twigFormResources = $container->hasParameter('twig.form.resources')
            ? $container->getParameter('twig.form.resources')
            : [];
        $container->setParameter(
            'twig.form.resources',
            array_merge(
                $twigFormResources,
                [
                    'OctavaMenuBundle:Form:menu_related_text_type.html.twig',
                ]
            )
        );
    }
}
