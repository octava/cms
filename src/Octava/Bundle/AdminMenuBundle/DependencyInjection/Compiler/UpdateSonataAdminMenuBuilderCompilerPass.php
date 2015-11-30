<?php
namespace Octava\Bundle\AdminMenuBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UpdateSonataAdminMenuBuilderCompilerPass implements CompilerPassInterface
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
        $container->setDefinition(
            'sonata.admin.menu_builder',
            $container->getDefinition('octava_admin_menu.builder')
        );
    }
}
