<?php
namespace Octava\Bundle\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OverrideLabelTranslatorStrategyCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $definition->addMethodCall(
                'setLabelTranslatorStrategy',
                [new Reference('octava_admin.translator.label_translator_strategy')]
            );
        }
    }
}
