<?php
namespace Octava\Bundle\MuiBundle\Router;

use JMS\I18nRoutingBundle\Router\PatternGenerationStrategyInterface;
use Octava\Bundle\MuiBundle\Entity\Office;
use Octava\Bundle\StructureBundle\Entity\Structure;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Translation\TranslatorInterface;

class PatternGenerationStrategy implements PatternGenerationStrategyInterface, ContainerAwareInterface
{
    const STRATEGY_PREFIX = 'prefix';
    const STRATEGY_PREFIX_EXCEPT_DEFAULT = 'prefix_except_default';
    const STRATEGY_CUSTOM = 'custom';

    private $strategy;
    private $translator;
    private $translationDomain;
    private $locales;
    private $cacheDir;
    private $defaultLocale;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Office[]
     */
    private $offices = [];

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
        /** @var FrozenParameterBag $parameterBag */
        $parameterBag = $this->container->getParameterBag();
        if (empty($this->offices)) {
            $this->offices = $this->container->get('doctrine')->getRepository(
                'OctavaMuiBundle:Office'
            )->getRoutingOffices();
        }

        $translation = null;
        if ($structureId = $route->getDefault(Structure::ROUTING_ID_NAME)) {
            $structureRepository = $this->container
                ->get('doctrine.orm.entity_manager')
                ->getRepository('OctavaStructureBundle:Structure');
            $translation = $structureRepository->getTranslations(
                $structureRepository->getById($structureId)
            );
        }

        foreach (array_keys($this->offices) as $locale) {

            if ($translation !== null
                && empty($translation[$locale]['state'])
            ) { // отключенная страница
                continue;
            }

            $office = $this->offices[$locale];

            $i18nPattern = $route->getPath();

            $paths = $route->getOption('translatable_path');

            if (!empty($paths[$locale])) {
                $i18nPattern = $paths[$locale];
            }

            if ($office->getIncludeLangInUrl()) {
                $i18nPattern = '/{_locale}'.$i18nPattern;
            }

            if (null !== ($prefix = $route->getOption('i18n_prefix'))) {
                $prefix = $parameterBag->resolveValue($prefix);
                $i18nPattern = $prefix.$i18nPattern;
            }

            $host = $office->getHost();
            if (empty($patterns[$i18nPattern][$host])) {
                $patterns[$i18nPattern][$host] = [];
            }
            $patterns[$i18nPattern][$host][] = $locale;
        }

        return $patterns;
    }

    /**
     * {@inheritDoc}
     */
    public function addResources(RouteCollection $i18nCollection)
    {
        foreach ($this->locales as $locale) {
            if (file_exists($metadata = $this->cacheDir.'/translations/catalogue.'.$locale.'.php.meta')) {
                foreach (unserialize(file_get_contents($metadata)) as $resource) {
                    $i18nCollection->addResource($resource);
                }
            }
        }
    }

    /**
     * @param ContainerInterface $container
     * @return self
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        return $this;
    }
}
