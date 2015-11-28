<?php

namespace Octava\Bundle\StructureBundle\DependencyInjection;

use Octava\Bundle\StructureBundle\Config\StructureConfig;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see
 * {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('octava_structure');

        $rootNode
            ->children()
            ->arrayNode(StructureConfig::KEY_ADDITIONAL_TEMPLATES)
            ->useAttributeAsKey('name')
            ->prototype('scalar')->end()
            ->end()
            ->scalarNode(StructureConfig::KEY_DEFAULT_TEMPLATE)
            ->defaultValue('OctavaStructureBundle:Default:index.html.twig')
            ->end()
            ->end();

        return $treeBuilder;
    }
}
