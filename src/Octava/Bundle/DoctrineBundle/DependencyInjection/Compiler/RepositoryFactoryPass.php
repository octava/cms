<?php
namespace Octava\Bundle\DoctrineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RepositoryFactoryPass implements CompilerPassInterface
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
        $def = $container->getDefinition('doctrine.orm.configuration');
        $def->addMethodCall(
            'setRepositoryFactory',
            [new Reference('octava_doctrine.orm_repository.octava_repository_factory')]
        );
    }
}
