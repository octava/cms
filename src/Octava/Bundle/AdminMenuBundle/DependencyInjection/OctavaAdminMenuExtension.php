<?php

namespace Octava\Bundle\AdminMenuBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OctavaAdminMenuExtension extends Extension
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

        $twigFormResources = $container->hasParameter('twig.form.resources')
            ? $container->getParameter('twig.form.resources')
            : [];
        $container->setParameter(
            'twig.form.resources',
            array_merge(
                $twigFormResources,
                [
                    'OctavaAdminMenuBundle:Form:octava_admin_menu_admin_class_choice_widget.html.twig',
                    'OctavaAdminMenuBundle:Form:octava_admin_menu_entity_widget.html.twig',
                ]
            )
        );
    }
}
