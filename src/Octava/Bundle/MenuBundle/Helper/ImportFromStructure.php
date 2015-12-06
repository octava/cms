<?php
namespace Octava\Bundle\MenuBundle\Helper;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\MenuBundle\Entity\Menu;
use Octava\Bundle\MenuBundle\Entity\MenuRepository;
use Octava\Bundle\MuiBundle\LocaleManager;
use Octava\Bundle\StructureBundle\Entity\Structure;

class ImportFromStructure
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var array
     */
    protected $updatedItems = [];

    /**
     * @param EntityManager $em
     * @param LocaleManager $localeManager
     */
    public function __construct(EntityManager $em, LocaleManager $localeManager)
    {
        $this->entityManager = $em;
        $this->localeManager = $localeManager;
    }

    /**
     * Импортировать данные из структуры
     * @param integer $menuParentId
     * @param string $location
     */
    public function import($menuParentId, $location)
    {
        /** @var MenuRepository $menuRepository */
        $menuRepository = $this->entityManager->getRepository('OctavaMenuBundle:Menu');

        /** @var Menu $menuParent */
        $menuParent = $menuRepository->find($menuParentId);

        $structureRepository = $this->entityManager->getRepository('OctavaStructureBundle:Structure');
        $structureTree = $structureRepository->getFlatTree(
            $menuParent && $menuParent->getStructure() ? $menuParent->getStructure()->getId() : 0
        );

        $this->addMenuTree($structureTree, $menuParent, $location);
    }

    /**
     * @return Structure[]
     */
    public function getUpdatedItems()
    {
        return $this->updatedItems;
    }

    /**
     * Добавить эементы структуры для
     * указанного родителя и местоположения
     * @param Structure[] $structureTree
     * @param Menu $menuParent
     * @param string $location
     */
    protected function addMenuTree($structureTree, $menuParent, $location)
    {
        $menuRepository = $this->entityManager->getRepository('OctavaMenuBundle:Menu');
        /** @var Menu[] $menuChildren */
        $menuChildren = $menuRepository->getByParentIdAndLocation($menuParent ? $menuParent->getId() : 0, $location);
        $existStructureIds = [];
        foreach ($menuChildren as $menuItem) {
            if ($menuItem->getStructure()) {
                $structureId = $menuItem->getStructure()->getId();
                $existStructureIds[$structureId] = $structureId;
            }
        }
        $structureParentId = $menuParent && $menuParent->getStructure()
            ? $menuParent->getStructure()->getId() : null;
        $structureAddElements = [];
        /** @var Structure $structureItem */
        foreach ($structureTree as $structureItem) {
            $currentParentId = $structureItem->getParent() && $structureItem->getParent()->getId()
                ? $structureItem->getParent()->getId() : null;
            if ($currentParentId == $structureParentId
                && !isset($existStructureIds[$structureItem->getId()])
            ) {
                $structureAddElements[] = $structureItem;
            }
        }

        if (count($structureAddElements)) {
            /** @var Menu[] $menuChildren */
            $menuChildren = [];
            $translationRepository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');

            foreach ($structureAddElements as $structureItem) {
                /** @var Structure $structureItem */
                $menu = new Menu();
                $menu->setState(true);
                $menu->setLocation($location);
                $menu->setStructure($structureItem);
                $menu->setTitle($structureItem->getTitle());
                $menu->setLink($structureItem->getPreparedPath());
                if ($menuParent) {
                    $menu->setParent($menuParent);
                }
                $this->entityManager->persist($menu);

                $structureTranslations = $translationRepository->findTranslations($structureItem);
                foreach ($this->localeManager->getAllAliases() as $localeAlias) {
                    if (!isset($structureTranslations[$localeAlias])) {
                        continue;
                    }
                    $translation = $structureTranslations[$localeAlias];

                    $path = empty($translation['path']) ? $structureItem->getPath() : $translation['path'];
                    $title = empty($translation['title']) ? $structureItem->getTitle() : $translation['title'];
                    $translationRepository->translate($menu, 'title', $localeAlias, $title);
                    $translationRepository->translate($menu, 'proxyTitle', $localeAlias, true);
                    $translationRepository->translate(
                        $menu,
                        'link',
                        $localeAlias,
                        $structureItem->getType() == Structure::TYPE_STRUCTURE_EMPTY ? '' : $path
                    );
                    $translationRepository->translate($menu, 'proxyLink', $localeAlias, true);
                }

                $menuChildren[] = $menu;
                $this->updatedItems[] = $menu;
            }

            $this->entityManager->flush();

            foreach ($menuChildren as $value) {
                if ($value->getStructure()) {
                    $this->addMenuTree($structureTree, $value, $location);
                }
            }
        }
    }
}
