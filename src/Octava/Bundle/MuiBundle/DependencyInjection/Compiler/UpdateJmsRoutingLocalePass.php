<?php
namespace Octava\Bundle\MuiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UpdateJmsRoutingLocalePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasParameter('jms_i18n_routing.default_locale')) {
            $ignoreDefaultLocaleUrl = $container
                    ->getParameter('jms_i18n_routing.strategy') == 'prefix_except_default';
            $managerDefinition = $container->getDefinition('octava_mui.locale_manager');
            $managerDefinition->replaceArgument(1, $container->getParameter('jms_i18n_routing.default_locale'));
            $managerDefinition->addArgument($ignoreDefaultLocaleUrl);
        }
    }
}
