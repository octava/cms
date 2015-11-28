<?php
namespace Octava\Bundle\StructureBundle\Routing;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\StructureBundle\Entity\Structure;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class StructureBundlesLoader extends DelegatingLoader
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        EntityManager $entityManager,
        LoaderResolverInterface $resolver
    ) {
        parent::__construct($resolver);

        $this->entityManager = $entityManager;
    }

    public function supports($resource, $type = null)
    {
        if ($type == 'octava_structure') {
            return true;
        }

        return false;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function load($resource, $type = null)
    {
        /** @var RouteCollection $collection */
        $collection = parent::load($resource, null);

        $straightRoutes = [];
        $relativeRoutes = [];

        foreach ($collection as $name => $route) {
            /** @var Route $route */
            if (preg_match('|\%([a-z_]+)\.path\%|ius', $route->getPath(), $matches)) {
                $path = $matches[1];
                $relativeRoutes[$path][$name] = $route;
            } else {
                $route->setDefault('_structure_type', $name);
                $straightRoutes[$name] = $route;
            }

            $collection->remove($name);
        }

        $structureRepository = $this->getEntityManager()
            ->getRepository('OctavaStructureBundle:Structure');
        /** @var Structure[] $structureData */
        $structureData = $structureRepository->getActiveListByTypes(array_keys($straightRoutes));

        foreach ($straightRoutes as $name => $route) {
            if (!isset($structureData[$name]) || $name == 'robo_structure_empty') {
                $controllerFakePath = str_replace(':', '_', $route->getDefault('_controller'));
                $route->setPath('/octava/structure/error404/'.$controllerFakePath)
                    ->setDefault('_controller', 'OctavaStructureBundle:Default:error404');
                $collection->add($name, $route);
                continue;
            }

            $counter = 0;
            foreach ($structureData[$name] as $structureItem) {
                /** @var Structure $structureItem */
                $workRoute = clone $route;

                $workRoute->setPath(rtrim($structureItem->getPath(), '/').'/'.ltrim($workRoute->getPath(), '/'));
                $workRoute->setDefault(Structure::ROUTING_ID_NAME, $structureItem->getId());
                $translatablePath = [];
                foreach ($structureRepository->getTranslatablePath($structureItem) as $l => $p) {
                    $path = rtrim($p, '/').'/'.ltrim($route->getPath(), '/');
                    $translatablePath[$l] = $path;
                }
                $workRoute->setOption('translatable_path', $translatablePath);

                $saveName = $structureItem->getRouteName();

                $collection->add($saveName, $workRoute);

                if (isset($relativeRoutes[$name])) {
                    foreach ($relativeRoutes[$name] as $relativeName => $relativeRoute) {
                        /** @var Route $workRelativeRoute */
                        $workRelativeRoute = clone $relativeRoute;
                        $workRelativeName = $counter == 0 ?
                            $relativeName : $relativeName.'_'.$counter;

                        $translatablePath = [];
                        foreach ($structureRepository->getTranslatablePath($structureItem) as $l => $p) {
                            $path = ltrim($workRelativeRoute->getPath(), '/');
                            $path = str_replace('%'.$name.'.path%', rtrim($p, '/'), $path);
                            $translatablePath[$l] = $path;
                        }
                        $path = trim($workRelativeRoute->getPath(), '/');
                        $path = str_replace('%'.$name.'.path%', rtrim($structureItem->getPath(), '/'), $path);
                        $workRelativeRoute->setPath($path)
                            ->setDefault(Structure::ROUTING_ID_NAME, $structureItem->getId())
                            ->setDefault('structureRelative', true)
                            ->setOption('translatable_path', $translatablePath);

                        $collection->add($workRelativeName, $workRelativeRoute);
                    }
                }

                $counter++;
            }
        }

        $collection->addResource(new FileResource(__FILE__));

        return $collection;
    }
}
