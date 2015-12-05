<?php
namespace Octava\Bundle\MenuBundle\Twig;

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Octava\Bundle\MenuBundle\Entity\Menu;
use Octava\Bundle\MenuBundle\Filter\FilterChain;
use Octava\Bundle\StructureBundle\Entity\Structure;
use Octava\Bundle\StructureBundle\StructureManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class MenuExtension extends \Twig_Extension
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var StructureManager
     */
    protected $structureManager;

    /**
     * @var FilesystemCache
     */
    protected $menuCache;

    /**
     * @var array
     */
    protected $selectedCache = [];

    /**
     * @var FilterChain
     */
    protected $filterChain;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    protected $cache = [];

    public function __construct(
        EntityManager $entityManager,
        StructureManager $structureManager,
        FilesystemCache $menuCache,
        FilterChain $filterChain,
        RequestStack $requestStack
    ) {
        $this->entityManager = $entityManager;
        $this->structureManager = $structureManager;
        $this->menuCache = $menuCache;
        $this->filterChain = $filterChain;
        $this->requestStack = $requestStack;
    }

    public function getName()
    {
        return 'octava_menu';
    }

    public function getFunctions()
    {
        return [
            'menu_structure' => new \Twig_SimpleFunction('menu_structure', [$this, 'getMenuStructure']),
            'submenu_structure' => new \Twig_SimpleFunction('submenu_structure', [$this, 'getSubMenuStructure']),
            'menu_selected_id' => new \Twig_SimpleFunction('menu_selected_id', [$this, 'getMenuSelectedId']),
            'menu_calculate_link' => new \Twig_SimpleFunction('menu_calculate_link', [$this, 'getMenuCalculateLink']),
            'target' => new \Twig_SimpleFunction('target', [$this, 'getTarget'], ['is_safe' => ['html']]),
        ];
    }

    public function getMenuStructure($location, $locale)
    {
        $selectedIds = $this->getSelectedIds($location);
        $tree = $this->getCachedMenu($location, $locale);

        return $this->setSelectedByLocation($tree, $selectedIds);
    }

    public function getSubMenuStructure($location, $locale)
    {
        $menu = $this->getCachedMenu($location, $locale);
        $selectedIds = $this->getSelectedIds($location);
        if (!sizeof($selectedIds)) {
            return [];
        }

        $rootId = array_shift($selectedIds);

        return $this->setSelectedByLocation($menu[$rootId]['children'], $selectedIds);
    }

    public function getMenuSelectedId($location)
    {
        $selectedIds = $this->getSelectedIds($location);

        return array_pop($selectedIds);
    }

    public function getMenuCalculateLink($item)
    {
        $result = '';
        if ($item['link'] && '/' != $item['link']
            && $item['structure_type'] != Structure::TYPE_STRUCTURE_EMPTY
        ) {
            $result = $item['link'];
        } elseif (!empty($item['children'])) {
            $child = array_shift($item['children']);
            $result = $child['link'];
        }

        return $result;
    }

    public function getTarget($url)
    {
        $local = true;
        if (preg_match('!^https?://!', $url)) {
            $parts = parse_url($url);
            /** @var Request $request */
            $request = $this->requestStack->getCurrentRequest();
            if ($parts !== false) {
                $host = $parts['host'];
                $local = $request->getHost() == $host;
            }
        }

        return $local ? '' : 'target="_blank"';
    }

    /**
     * Найти список выбранных элементов по текущему URL
     * @param string $location
     * @return array
     */
    protected function getSelectedIds($location)
    {
        if (isset($this->selectedCache[$location])) {
            return $this->selectedCache[$location];
        }

        $structureItem = $this->structureManager->getCurrentItem();
        if (is_null($structureItem)) {
            return $this->selectedCache[$location] = [];
        }

        $menuRepository = $this->entityManager->getRepository('OctavaMenuBundle:Menu');
        $items = $menuRepository->getActiveByStructureIdAndLocation($structureItem->getId(), $location);

        if (!sizeof($items)) {
            return $this->selectedCache[$location] = [];
        }

        $firstItem = array_shift($items);

        $result = [$firstItem->getId() => $firstItem->getId()];
        $currentItem = $firstItem;
        while ($parentItem = $currentItem->getParent()) {
            /** @var Menu $parentItem */
            $result[$parentItem->getId()] = $parentItem->getId();
            $currentItem = $parentItem;
        }

        $result = array_reverse($result, true);

        return $this->selectedCache[$location] = $result;
    }

    /**
     * Установить флаг выбранного
     * элемента для рекурсивного массива меню
     * @param array $data
     * @param array $selectedIds
     * @return array
     */
    protected function setSelectedByLocation($data, $selectedIds)
    {
        foreach ($data as $id => $value) {
            if (in_array($id, $selectedIds)) {
                $data[$id]['selected'] = true;

                if (sizeof($data[$id]['children'])) {
                    $data[$id]['children'] = $this->setSelectedByLocation($value['children'], $selectedIds);
                }
            }
        }

        return $data;
    }

    protected function getCachedMenu($location, $locale)
    {
        $tree = $this->entityManager->getRepository('OctavaMenuBundle:Menu')
            ->getActiveTreeByLocale($location, $locale);
        $tree = $this->filterChain->filter($tree, $location, $locale);

        return $tree;
    }
}
