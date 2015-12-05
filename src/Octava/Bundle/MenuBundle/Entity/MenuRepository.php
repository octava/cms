<?php

namespace Octava\Bundle\MenuBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Gedmo\Translatable\TranslatableListener;
use Octava\Bundle\StructureBundle\Entity\Structure;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * MenuRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MenuRepository extends EntityRepository implements ContainerAwareInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Выбрать дерево меню, подготовленное
     * для отображения в селекбоксе
     * @param string $location
     * @param integer $currentId
     * @return array
     */
    public function getFlatTreeForSelect($location, $currentId)
    {
        $tree = $this->getFlatTree($location, 0);
        $result = [];

        $excludeParentIds = [];
        foreach ($tree as $item) {
            /** @var Menu $item */
            if ($item->getId() == $currentId) {
                $excludeParentIds[] = $currentId;
                continue;
            }
            $itemParentId = $item->getParent() ? $item->getParent()->getId() : 0;
            if (in_array($itemParentId, $excludeParentIds)) {
                $excludeParentIds[] = $item->getId();
                continue;
            }

            $result[$item->getId()] = str_repeat('....', $item->getLevel() - 1).$item->getTitle();
        }

        return $result;
    }

    /**
     * Выбрать дерево меню в виде плоского списка
     * Элементы в результате будут расположены
     * от корня к листям в порядке полного обхода
     * @param string $location
     * @param int $parentId
     * @param int $level
     * @param null $rows
     * @return array
     */
    public function getFlatTree($location, $parentId = 0, $level = 1, $rows = null)
    {
        $result = [];
        $queryBuilder = $this->createQueryBuilder('m');

        if (is_null($rows)) {
            /** @var Menu[] $rows */
            $rows = $queryBuilder->andWhere('m.location = :location')
                ->setParameter('location', $location)
                ->orderBy('m.position')
                ->getQuery()
                ->getResult();
        }

        foreach ($rows as $item) {
            $pid = $item->getParent() instanceof Menu ? $item->getParent()->getId() : null;
            if (intval($parentId) == intval($pid)) {
                $item->setLevel($level);
                $result[$item->getId()] = $item;
                $children = $this->getFlatTree($location, $item->getId(), $level + 1, $rows);
                $result += $children;
            }
        }

        return $result;
    }

    /**
     * @param string $location
     * @param $locale
     * @return array
     */
    public function getActiveTreeByLocale($location, $locale)
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->select('m,s')
            ->where('m.state = 1')
            ->leftJoin('m.structure', 's')
            ->andWhere('m.location = :location')
            ->setParameter('location', $location)
            ->orderBy('m.position');

        $query = $queryBuilder->getQuery();
        $query
            ->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
            );
        $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale);
        $query->setHint(
            TranslatableListener::HINT_FALLBACK,
            $this->getContainer()->getParameter('stof_doctrine_extensions.translation_fallback')
        );

        $result = $this->getTreeFromFlatList($query->getResult());

        return $result;
    }

    /**
     * Получить массив элементов
     * по ID родителя и месторасположению
     * @param integer $parentId
     * @param string $location
     * @return array
     */
    public function getByParentIdAndLocation($parentId, $location)
    {
        $queryBuilder = $this->createQueryBuilder('m');

        if ($parentId != 0) {
            $queryBuilder->where('IDENTITY(m.parent) = :parent_id')
                ->setParameter('parent_id', $parentId);
        } else {
            $queryBuilder->where('IDENTITY(m.parent) IS NULL');
        }

        return $queryBuilder->andWhere('m.location = :location')
            ->setParameter('location', $location)
            ->orderBy('m.position')
            ->getQuery()->getResult();
    }

    /**
     * Получить список объектов
     * структуры по родительскому ID
     * @param $parentId
     * @param $location
     * @return array
     */
    public function getActiveByParentId($parentId, $location)
    {
        $queryBuilder = $this->createQueryBuilder('m');
        if (is_null($parentId) || $parentId == 0) {
            $queryBuilder->where('IDENTITY(m.parent) IS NULL');
        } else {
            $queryBuilder->where('IDENTITY(m.parent) = :parent_id')
                ->setParameter('parent_id', $parentId);
        }

        return $queryBuilder->andWhere('m.location = :location')
            ->setParameter('location', $location)
            ->andWhere('m.state = 1')
            ->orderBy('m.position')
            ->getQuery()->getResult();
    }

    /**
     * @param $structureId
     * @param $location
     * @return Menu[]
     */
    public function getActiveByStructureIdAndLocation($structureId, $location)
    {
        return $this->createQueryBuilder('m')
            ->where('m.location = :location')
            ->andWhere('IDENTITY(m.structure) = :structure_id')
            ->setParameters(['location' => $location, 'structure_id' => $structureId])
            ->andWhere('m.state = 1')
            ->getQuery()->getResult();
    }

    /**
     * Получить список эдементов меню
     * связанных с указанным по ID элементом структуры
     * @param $structureId
     * @return array
     */
    public function getByStructureId($structureId)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('IDENTITY(m.structure) = :structureId')
            ->setParameter('structureId', $structureId)
            ->getQuery()->getResult();
    }

    /**
     * @param Menu[] $list
     * @param int $parentId
     * @param int $level
     * @return array
     */
    protected function getTreeFromFlatList(array $list, $parentId = 0, $level = 1)
    {
        $ret = [];
        foreach ($list as $item) {
            $itemParentId = $item->getParent() ? $item->getParent()->getId() : 0;
            if ($itemParentId != $parentId) {
                continue;
            }

            $structure = $item->getStructure();

            $structureId = $type = null;
            if ($structure instanceof Structure) {
                if (!$structure->getState()) {
                    continue;
                }
                $type = $structure->getType();
                $structureId = $structure->getId();
            }

            $link = $item->getLink();
            if ('/' != substr($link, -1, 1) && false === strpos(basename($link), '.')) {
                $link .= '/';
            }

            $ret[$item->getId()] = [
                'id' => $item->getId(),
                'parent_id' => $itemParentId,
                'title' => $item->getTitle(),
                'link' => $link,
                'structure_id' => $structureId,
                'structure_type' => $type,
                'level' => $level,
                'selected' => false,
                'is_test' => $item->getIsTest(),
                'children' => $this->getTreeFromFlatList($list, $item->getId(), $level + 1),
            ];
        }

        return $ret;
    }
}
