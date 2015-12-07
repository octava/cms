<?php

namespace Octava\Bundle\StructureBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\Entity\Translation;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * StructureRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StructureRepository extends EntityRepository implements ContainerAwareInterface
{
    /**
     * @var Container
     */
    protected $container;
    /**
     * @var Structure[]
     */
    private $fullStructure;

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Выбрать дерево структуры,
     * подготовленное для отображения в селекбоксе
     * @param integer $currentId
     * @return array
     */
    public function getFlatTreeForSelect($currentId)
    {
        $tree = $this->getFlatTree();
        $result = [];

        $excludeParentIds = [];
        foreach ($tree as $item) {
            /** @var Structure $item */
            if ($item->getId() == $currentId) {
                $excludeParentIds[] = $currentId;
                continue;
            }

            if ($item->getParent() && in_array($item->getParent()->getId(), $excludeParentIds)) {
                $excludeParentIds[] = $item->getId();
                continue;
            }

            $result[$item->getId()] = str_repeat('....', $item->getLevel() - 1).$item->getTitle();
        }

        return $result;
    }

    /**
     * Выбрать дерево структуры в виде плоского списка
     * Элементы в результате будут расположены
     * от корня к листям в порядке полного обхода
     * @param int $parentId
     * @param int $level
     * @param array $structures
     * @return Structure[]
     */
    public function getFlatTree($parentId = 0, $level = 1, $structures = [])
    {
        if (empty($structures)) {
            /** @var Structure[] $structures */
            $structures = $this->getFullStructure();
        }
        $ret = [];
        foreach ($structures as $structure) {
            $structureParentId = $structure->getParent() ? intval($structure->getParent()->getId()) : 0;
            if ($structureParentId == $parentId) {
                $structure->setLevel($level);
                $ret[$structure->getId()] = $structure;
                $ret += $this->getFlatTree($structure->getId(), $level + 1, $structures);
            }
        }

        return $ret;
    }

    /**
     * @return Structure[]
     */
    public function getFullStructure()
    {
        if (empty($this->fullStructure)) {
            $tableName = $this->getClassMetadata()->getTableName();
            $tableExists = $this->getEntityManager()
                ->getConnection()
                ->getSchemaManager()
                ->tablesExist([$tableName]);

            $data = [];
            if ($tableExists) {
                $queryBuilder = $this->createQueryBuilder('s')
                    ->select('s')
                    ->orderBy('s.id', 'ASC');
                $query = $queryBuilder->getQuery();
                $query->setHint(
                    Query::HINT_CUSTOM_OUTPUT_WALKER,
                    'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
                );
                $locale = $this->getContainer()->getParameter('kernel.default_locale');
                if ($this->getContainer()->get('request_stack')->getCurrentRequest()) {
                    $locale = $this->getContainer()->get('request_stack')
                        ->getCurrentRequest()->getLocale();
                }
                $query->setHint(
                    TranslatableListener::HINT_TRANSLATABLE_LOCALE,
                    $locale
                );
                $query->setHint(
                    TranslatableListener::HINT_FALLBACK,
                    $this->getContainer()
                        ->getParameter('stof_doctrine_extensions.translation_fallback')
                );

                $listener = $this->getContainer()
                    ->get('stof_doctrine_extensions.listener.translatable');
                //выключаем событие postLoad,
                // которые делает много запросов
                $listener->setSkipOnLoad(true);

                $data = $query->getResult();

                $listener = $this->getContainer()
                    ->get('stof_doctrine_extensions.listener.translatable');
                $listener->setSkipOnLoad(false);
            }

            $this->fullStructure = [];
            foreach ($data as $item) {
                /** @var Structure $item */
                $this->fullStructure[$item->getId()] = $item;
            }
        }

        return $this->fullStructure;
    }

    /**
     * Обновить поле пути всех узлов структуры
     * @param int $parentId
     */
    public function updateAllPathValues($parentId = 0)
    {
        $this->fullStructure = [];
        foreach ($this->getFlatTree($parentId) as $item) {
            $this->updatePath($item);
        }
    }

    /**
     * Обновляет пути с учетом локализации,
     * flush не делает
     * @param Structure $structure
     */
    public function updatePath(Structure $structure)
    {
        $translations = $this->getTranslations($structure);
        $parent = $structure->getParent();
        $parentTranslations = [];
        if ($parent instanceof Structure) {
            $parentTranslations = $this->getTranslations($parent);
        }
        $locales = array_unique(
            array_merge(
                array_keys($parentTranslations),
                array_keys($translations)
            )
        );
        /** @var TranslationRepository $repository */
        $repository = $this->getEntityManager()->getRepository(Translation::class);
        foreach ($locales as $locale) {
            $prefix = '/';
            if (!empty($parentTranslations[$locale]['path'])) {
                $prefix = $parentTranslations[$locale]['path'];
            } elseif ($parent instanceof Structure) {
                $prefix = $parent->getPath();
            }
            $alias = !empty($translations[$locale]['alias'])
                ? $translations[$locale]['alias'] : $structure->getAlias();
            $repository->translate(
                $structure,
                'path',
                $locale,
                rtrim($prefix, '/').'/'.$alias.'/'
            );
        }
    }

    /**
     * Получить объект структуры по ID
     * @param $id
     * @return Structure
     */
    public function getById($id)
    {
        $result = null;
        $fullStructure = $this->getFullStructure();
        if (array_key_exists($id, $fullStructure)) {
            $result = $fullStructure[$id];
        }

        return $result;
    }

    /**
     * Получить список объектов
     * структуры по родительскому ID
     * @param $parentId
     * @return array
     */
    public function getActiveByParentId($parentId)
    {
        $fullStructure = $this->getFullStructure();
        $result = [];
        foreach ($fullStructure as $structure) {
            if (!$structure->getState()) {
                continue;
            }

            if (!empty($parentId) && $parentId != $structure->getParent()) {
                continue;
            }

            $result[] = $structure;
        }

        return $result;
    }

    /**
     * Получть список объектов
     * структуры по типам контента
     * @param array $types
     * @return array
     */
    public function getActiveListByTypes(array $types)
    {
        $fullStructure = $this->getFullStructure();

        $result = [];
        foreach ($fullStructure as $structure) {
            if (in_array($structure->getType(), $types)
                && $this->getCombinedState($structure)
            ) {
                $result[$structure->getType()][] = $structure;
            }
        }

        return $result;
    }

    /**
     * Возвращает полный путь до элемента,
     * включая сам элемент,
     * в виде массива объектов Structure
     * @param Structure $item
     * @return array
     */
    public function getBreadcrumbsPath(Structure $item)
    {
        $result = [$item];
        $parentItem = $item;
        while ($parentItem = $parentItem->getParent()) {
            $result[] = $parentItem;
            if ($parentItem->getType() == Structure::TYPE_STRUCTURE_EMPTY) {
                /** @var Structure[] $children */
                $children = $parentItem->getChildren();
                foreach ($children as $child) {
                    if ($child->getState()
                        && $child->getType() != Structure::TYPE_STRUCTURE_EMPTY
                    ) {
                        $this->getEntityManager()->detach($parentItem);
                        $parentItem->setPath($child->getPath());
                        $parentItem->setType($child->getType());
                        break;
                    }
                }
            }
        }

        return array_reverse($result);
    }

    /**
     * @param Structure $structure
     * @return array
     */
    public function getTranslatablePath(Structure $structure)
    {
        $ret = [];
        $translations = $this->getTranslations($structure);
        foreach ($translations as $locale => $translation) {
            if (empty($translation['path'])) {
                continue;
            }
            $ret[$locale] = $translation['path'];
        }

        return $ret;
    }

    /**
     * @param Structure $structure
     * @return array
     */
    public function getTranslations(Structure $structure)
    {
        $em = $this->getEntityManager();
        /** @var TranslationRepository $translationRepository */
        $translationRepository = $em->getRepository(Translation::class);
        $translations = $translationRepository->findtranslations($structure);

        return $translations;
    }

    /**
     * Возвращает true, если структура
     * активна хотя бы в одном переводе
     * @param Structure $structure
     * @return bool
     */
    public function getCombinedState(Structure $structure)
    {
        $translations = $this->getTranslations($structure);
        $ret = $structure->getState();
        foreach ($translations as $locale => $translation) {
            $ret = $ret || !empty($translation['state']);
        }

        return $ret;
    }
}
