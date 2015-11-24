<?php
namespace Octava\Bundle\MuiBundle\DependencyInjection\Compiler;

use Octava\Bundle\MuiBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UpdateTranslatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('translator.default')
            ->setClass(Translator::class);
    }
}
