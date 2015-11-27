<?php

namespace Octava\Bundle\AdminBundle;

use Octava\Bundle\AdminBundle\DependencyInjection\Compiler\OverrideLabelTranslatorStrategyCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OctavaAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideLabelTranslatorStrategyCompilerPass());
    }
}
