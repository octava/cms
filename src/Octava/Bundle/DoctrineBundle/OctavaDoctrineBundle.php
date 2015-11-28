<?php

namespace Octava\Bundle\DoctrineBundle;

use Octava\Bundle\DoctrineBundle\DependencyInjection\Compiler\RepositoryFactoryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OctavaDoctrineBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RepositoryFactoryPass());
    }
}
