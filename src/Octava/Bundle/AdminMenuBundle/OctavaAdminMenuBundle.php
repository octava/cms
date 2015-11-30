<?php

namespace Octava\Bundle\AdminMenuBundle;

use Octava\Bundle\AdminMenuBundle\DependencyInjection\Compiler\UpdateSonataAdminMenuBuilderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OctavaAdminMenuBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new UpdateSonataAdminMenuBuilderCompilerPass());
    }
}
