<?php

namespace Octava\Bundle\MuiBundle\DependencyInjection;

use Octava\Bundle\MuiBundle\Dict\Currencies;
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
        $rootNode = $treeBuilder->root('octava_mui');

        $searchTypes = ['host', 'locale'];

        $rootNode
            ->children()
            ->booleanNode('set_locale_by_office')->defaultFalse()->end()
            ->arrayNode('search_types')
            ->prototype('scalar')
            ->validate()
            ->ifNotInArray($searchTypes)
            ->thenInvalid(
                'The office search type %s is not supported. Allowed types: ' . json_encode($searchTypes)
            )
            ->end()
            ->end()
            ->defaultValue(['host'])
            ->end()
            ->arrayNode('currencies')
            ->prototype('scalar')->end()
            ->defaultValue([Currencies::USD, Currencies::EUR, Currencies::RUB])
            ->end()
            ->arrayNode('url_ignore_prefixes')
            ->prototype('scalar')->end()
            ->defaultValue(['/admin', '/_'])
            ->end()
            ->end();

        return $treeBuilder;
    }
}
