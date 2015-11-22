<?php
namespace Octava\Bundle\MuiBundle\Router;

use JMS\I18nRoutingBundle\Router\DefaultPatternGenerationStrategy as DefaultPatternGenerationStrategyBase;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Translation\TranslatorInterface;

class DefaultPatternGenerationStrategy extends DefaultPatternGenerationStrategyBase
{
    private $strategy;
    private $translator;
    private $translationDomain;
    private $locales;
    private $cacheDir;
    private $defaultLocale;
    private $container;

    public function __construct(
        $strategy,
        TranslatorInterface $translator,
        array $locales,
        $cacheDir,
        $translationDomain = 'routes',
        $defaultLocale = 'en'
    ) {
        $this->strategy = $strategy;
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
        $this->locales = $locales;
        $this->cacheDir = $cacheDir;
        $this->defaultLocale = $defaultLocale;
    }

    public function generateI18nPatterns($routeName, Route $route)
    {
        $patterns = [];
        foreach ($route->getOption('i18n_locales') ?: $this->locales as $locale) {
            // if no translation exists, we use the current pattern
            if ($routeName === $i18nPattern = $this->translator
                    ->trans($routeName, [], $this->translationDomain, $locale)
            ) {
                $i18nPattern = $route->getPath();
            }

            // prefix with locale if requested
            if (self::STRATEGY_PREFIX === $this->strategy
                || self::STRATEGY_PREFIX === $route->getOption('i18n_strategy')
                || (self::STRATEGY_PREFIX_EXCEPT_DEFAULT === $this->strategy && $this->defaultLocale !== $locale)
            ) {
                $i18nPattern = '/' . $locale . $i18nPattern;
                if (null !== $route->getOption('i18n_prefix')) {
                    $prefix = $route->getOption('i18n_prefix');
                    /** @var FrozenParameterBag $parameterBag */
                    $parameterBag = $this->container->getParameterBag();
                    $prefix = $parameterBag->resolveValue($prefix);
                    $i18nPattern = $prefix . $i18nPattern;
                }
            }

            $patterns[$i18nPattern][] = $locale;
        }

        return $patterns;
    }

    public function addResources(RouteCollection $i18nCollection)
    {
        foreach ($this->locales as $locale) {
            if (file_exists($metadata = $this->cacheDir . '/translations/catalogue.' . $locale . '.php.meta')) {
                foreach (unserialize(file_get_contents($metadata)) as $resource) {
                    $i18nCollection->addResource($resource);
                }
            }
        }
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
