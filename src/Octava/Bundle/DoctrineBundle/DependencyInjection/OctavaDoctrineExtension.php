<?php

namespace Octava\Bundle\DoctrineBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OctavaDoctrineExtension extends Extension
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
    }

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('doctrine');
        $hasDefault = false;
        foreach ($configs as $config) {
            if (isset($config['orm']['entity_managers']['default'])) {
                $hasDefault = true;
                break;
            }
        }
        if (!$hasDefault) {
            return;
        }
        $allBundles = array_keys($container->getParameter('kernel.bundles'));
        $definedBundles = [];
        foreach ($configs as $config) {
            if (empty($config['orm']['entity_managers']) || !is_array($config['orm']['entity_managers'])) {
                continue;
            }
            foreach ($config['orm']['entity_managers'] as $em) {
                if (empty($em['mappings']) || !is_array($em['mappings'])) {
                    continue;
                }
                $definedBundles = array_merge($definedBundles, array_keys($em['mappings']));
            }
        }
        $definedBundles = array_unique($definedBundles);
        $mappings = [];
        foreach (array_diff($allBundles, $definedBundles) as $bundle) {
            $mappings[$bundle] = null;
        }
        $newConfig = [
            'orm' => [
                'entity_managers' => [
                    'default' => [
                        'mappings' => $mappings
                    ]
                ]
            ]
        ];
        $container->prependExtensionConfig('doctrine', $newConfig);
    }
}
