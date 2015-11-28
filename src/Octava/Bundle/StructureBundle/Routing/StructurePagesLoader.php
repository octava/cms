<?php
namespace Octava\Bundle\StructureBundle\Routing;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\StructureBundle\Config\StructureConfig;
use Octava\Bundle\StructureBundle\Entity\Structure;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class StructurePagesLoader extends FileLoader
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var StructureConfig
     */
    protected $structureConfig;

    public function __construct(
        FileLocatorInterface $locator,
        EntityManager $entityManager,
        StructureConfig $structureConfig
    ) {
        parent::__construct($locator);

        $this->entityManager = $entityManager;
        $this->structureConfig = $structureConfig;
    }

    /**
     * @return StructureConfig
     */
    public function getStructureConfig()
    {
        return $this->structureConfig;
    }

    public function supports($resource, $type = null)
    {
        if ($type == 'octava_structure_pages') {
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
        $collection = new RouteCollection();
        $structureRepository = $this->getEntityManager()
            ->getRepository('OctavaStructureBundle:Structure');
        $tree = $structureRepository->getFlatTree();

        foreach ($tree as $item) {
            if ($item->getType() == 'page' && $structureRepository->getCombinedState($item)) {
                $routePattern = $item->getPath();
                if ('/' != substr($routePattern, -1, 1) && false === strpos(basename($routePattern), '.')) {
                    $routePattern .= '/';
                }
                $route = new Route($routePattern);
                $route->setDefault(
                    '_controller',
                    $this->getStructureConfig()->getDefaultTemplate()
                )
                    ->setDefault('id', $item->getId())
                    ->setDefault(Structure::ROUTING_ID_NAME, $item->getId())
                    ->setDefault('_structure_type', 'page')
                    ->setOption('translatable_path', $structureRepository->getTranslatablePath($item));

                $collection->add($item->getRouteName(), $route);
            }
        }

        $collection->addResource(new FileResource(__FILE__));

        return $collection;
    }
}
