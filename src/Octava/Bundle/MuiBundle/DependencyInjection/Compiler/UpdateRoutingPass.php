<?php
namespace Octava\Bundle\MuiBundle\DependencyInjection\Compiler;

use Octava\Bundle\MuiBundle\Router\I18nLoader;
use Octava\Bundle\MuiBundle\Router\LocaleChoosingListener;
use Octava\Bundle\MuiBundle\Routing\Matcher\Dumper\PhpMatcherDumper;
use Octava\Bundle\MuiBundle\Routing\RedirectableUrlMatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class UpdateRoutingPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $container->setAlias(
            'jms_i18n_routing.pattern_generation_strategy',
            'octava_mui.router.pattern_generation_strategy'
        );

        $container->setAlias('router', 'octava_mui.router.i18n_router');
        $translatorDef = $container->findDefinition('translator');
        if ('%translator.identity.class%' === $translatorDef->getClass()) {
            throw new \RuntimeException(
                'The JMSI18nRoutingBundle requires Symfony2\'s translator to be enabled.'
                .' Please make sure to un-comment the respective section in the framework config.'
            );
        }

        $loaderDef = $container->getDefinition('jms_i18n_routing.loader');
        $loaderDef->setClass(I18nLoader::class);
        $loaderDef->addMethodCall('setEntityManager', [new Reference('doctrine.orm.entity_manager')]);

        $router = $container->getDefinition('router.default');
        $arg = $router->getArgument(2);
        $arg['matcher_base_class'] = RedirectableUrlMatcher::class;
        $arg['matcher_dumper_class'] = PhpMatcherDumper::class;
        $router->replaceArgument(2, $arg);

        $container->getDefinition('jms_i18n_routing.locale_choosing_listener')
            ->setClass(LocaleChoosingListener::class)
            ->replaceArgument(1, new Reference('octava_mui.locale_manager'));

        if ($container->hasParameter('jms_i18n_routing.default_locale')) {
            $ignoreDefaultLocaleUrl = $container->getParameter('jms_i18n_routing.strategy') == 'prefix_except_default';
            $managerDefinition = $container->getDefinition('octava_mui.locale_manager');
            $managerDefinition->replaceArgument(1, $container->getParameter('jms_i18n_routing.default_locale'));
            $managerDefinition->addArgument($ignoreDefaultLocaleUrl);
        }
    }
}
