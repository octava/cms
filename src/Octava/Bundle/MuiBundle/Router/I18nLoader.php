<?php
namespace Octava\Bundle\MuiBundle\Router;

use Doctrine\ORM\EntityManager;
use JMS\I18nRoutingBundle\Router\PatternGenerationStrategyInterface;
use JMS\I18nRoutingBundle\Router\RouteExclusionStrategyInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class I18nLoader
{
    const ROUTING_PREFIX = '__RG__';

    private $routeExclusionStrategy;
    private $patternGenerationStrategy;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        RouteExclusionStrategyInterface $routeExclusionStrategy,
        PatternGenerationStrategyInterface $patternGenerationStrategy
    ) {
        $this->routeExclusionStrategy = $routeExclusionStrategy;
        $this->patternGenerationStrategy = $patternGenerationStrategy;
    }

    public function load(RouteCollection $collection)
    {
        $i18nCollection = new RouteCollection();
        foreach ($collection->getResources() as $resource) {
            $i18nCollection->addResource($resource);
        }
        $this->patternGenerationStrategy->addResources($i18nCollection);

        $existedHomepage = [];
        foreach ($collection->all() as $name => $route) {
            if ($this->routeExclusionStrategy->shouldExcludeRoute($name, $route)) {
                $i18nCollection->add($name, $route);
                continue;
            }

            $patters = $this->patternGenerationStrategy->generateI18nPatterns($name, $route);

            foreach ($patters as $pattern => $hostLocales) {
                // If this pattern is used for more than one locale, we need to keep the original route.
                // We still add individual routes for each locale afterwards for faster generation.
                foreach ($hostLocales as $host => $locales) {

                    if (count($locales) > 1) {
                        $catchMultipleRoute = clone $route;
                        $catchMultipleRoute->setPath($pattern);
                        $catchMultipleRoute->setHost($host);
                        $catchMultipleRoute->setRequirement('_locale', implode('|', $locales));
                        $catchMultipleRoute->setOption('original_name', $name)->setOption('locales', $locales);
                        $i18nCollection->add(
                            implode('_', $locales).I18nLoader::ROUTING_PREFIX.$name,
                            $catchMultipleRoute
                        );
                    } elseif (count($locales) == 1) {
                        $catchRoute = clone $route;
                        $catchRoute->setPath($pattern);
                        $catchRoute->setHost($host);
                        $catchRoute->setDefault('_locale', $locales[0]);
                        $catchRoute->setRequirement('_locale', $locales[0]);
                        $catchRoute->setOption('original_name', $name)->setOption('locales', $locales);
                        $i18nCollection->add(implode('_', $locales).I18nLoader::ROUTING_PREFIX.$name, $catchRoute);
                    }
                    if ($pattern == '/') {
                        $existedHomepage[] = $host;
                    }
                }
            }
        }

        $offices = $this->entityManager->getRepository('OctavaMuiBundle:Office')->getRoutingOffices();
        foreach ($offices as $locale => $office) {
            if ($office->getIncludeLangInUrl() && !in_array($office->getHost(), $existedHomepage)) {
                $redirectRoute = new Route(
                    '/',
                    [
                        '_controller' => 'FrameworkBundle:Redirect:urlRedirect',
                        'path' => '/'.$locale.'/',
                        'permanent' => true,
                    ]
                );
                $redirectRoute->setHost($office->getHost());
                $i18nCollection->add($locale.I18nLoader::ROUTING_PREFIX.'redirect_to_root', $redirectRoute);
            }
        }

        return $i18nCollection;
    }

    /**
     * @param EntityManager $entityManager
     * @return $this
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }
}
