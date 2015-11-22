<?php

namespace Octava\Bundle\AdministratorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OctavaAdministratorExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $definition = new Definition(
            $container->getParameter('octava_administrator.config.administrator.class'),
            [
                $config['default_show_hidden'],
                $config['whitelist']
            ]
        );
        $container->setDefinition('octava_administrator.config.administrator', $definition);

        $twigFormResources = $container->hasParameter('twig.form.resources')
            ? $container->getParameter('twig.form.resources')
            : [];
        $container->setParameter(
            'twig.form.resources',
            array_merge($twigFormResources, ['OctavaAdministratorBundle:Form:acl_resources_widget.html.twig'])
        );
    }

    public function prepend(ContainerBuilder $container)
    {
        $configName = 'sonata_admin';
        $configs = [];
        $configs['security']['handler'] = 'octava_administrator.security_handler.administrator';
        $container->prependExtensionConfig($configName, $configs);

        $configName = 'fos_user';
        $configs = [];
        $configs['user_class'] = 'Octava\\Bundle\\AdministratorBundle\\Entity\\Administrator';
        $configs['group']['group_class'] = 'Octava\\Bundle\\AdministratorBundle\\Entity\\Group';
        $container->prependExtensionConfig($configName, $configs);

        $configName = 'sonata_user';
        $configs = [];
        $configs['class']['user'] = 'Octava\\Bundle\\AdministratorBundle\\Entity\\Administrator';
        $configs['class']['group'] = 'Octava\\Bundle\\AdministratorBundle\\Entity\\Group';
        $configs['admin']['user']['class'] = 'Octava\\Bundle\\AdministratorBundle\\Admin\\AdministratorAdmin';
        $configs['admin']['user']['controller'] = 'OctavaAdministratorBundle:AdministratorAdmin';
        $configs['admin']['user']['translation'] = 'OctavaAdministratorBundle';
        $configs['admin']['group']['class'] = 'Octava\\Bundle\\AdministratorBundle\\Admin\\GroupAdmin';
        $configs['admin']['group']['controller'] = 'OctavaAdministratorBundle:GroupAdmin';
        $configs['admin']['group']['translation'] = 'OctavaAdministratorBundle';
        $container->prependExtensionConfig($configName, $configs);
    }
}
