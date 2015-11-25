<?php

namespace Octava\Bundle\MuiBundle;

use Octava\Bundle\MuiBundle\DependencyInjection\Compiler\UpdateTranslatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OctavaMuiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new UpdateTranslatorPass());
    }
}
