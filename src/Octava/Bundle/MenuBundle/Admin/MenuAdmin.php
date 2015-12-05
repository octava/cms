<?php

namespace Octava\Bundle\MenuBundle\Admin;

use Doctrine\ORM\EntityManager;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Octava\Bundle\MenuBundle\Entity\Menu;
use Octava\Bundle\MenuBundle\Entity\MenuRepository;
use Octava\Bundle\MenuBundle\MenuManager;
use Octava\Bundle\MuiBundle\Form\TranslationMapper;
use Octava\Bundle\StructureBundle\Entity\StructureRepository;
use Octava\Bundle\TreeBundle\TreeManager;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;

class MenuAdmin extends Admin
{
    protected $translationDomain = 'OctavaMenuBundle';

    /**
     * @var MenuManager
     */
    protected $menuManager;

    /**
     * @var TreeManager
     */
    protected $treeManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var TranslationMapper
     */
    protected $translationMapper;

    public function configure()
    {
        $container = $this->getConfigurationPool()->getContainer();
        $this->menuManager = $container->get('octava_menu.menu_manager');
        $this->treeManager = $container->get('octava_tree.tree_manager');
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->translationMapper = $container->get('octava_mui.form.translation_mapper');
    }

    public function getNewInstance()
    {
        $object = parent::getNewInstance();

        if ($this->getRequest()->getMethod() !== 'POST') {
            if ($parentId = $this->getFilteredParentId()) {
                $object->setParentId($parentId);

                /** @var ModelManager $modelManager */
                $modelManager = $this->getModelManager();
                $parent = $modelManager
                    ->getEntityManager($this->getClass())
                    ->getRepository($this->getClass())
                    ->find($parentId);

                $object->setParent($parent);
            }
        }

        return $object;
    }

    /**
     * @param Menu $object
     * @return mixed|void
     */
    public function prePersist($object)
    {
        $this->fixObjectProperties($object);
        if ($object->getParent()) {
            $parent = $this->getMenuRepository()->find($object->getParent()->getId());
            $object->setParent($parent);
        }
    }

    /**
     * @param Menu $object
     * @return mixed|void
     */
    public function preUpdate($object)
    {
        $this->fixObjectProperties($object);
    }

    public function postPersist($object)
    {
        $this->clearCache();
    }

    public function clearCache()
    {
        $this->menuManager->clearCache();
    }

    public function postUpdate($object)
    {
        $this->clearCache();
    }

    public function postRemove($object)
    {
        $this->clearCache();
    }

    public function getMenuTree()
    {
        /** @var MenuRepository $repository */
        $repository = $this->getMenuRepository();
        $ret = $repository->getFlatTree($this->getFilteredLocation());

        return $ret;
    }

    public function getFilteredParentId()
    {
        $parameters = $this->getFilterParameters();
        $result = isset($parameters['parent_id']) ? $parameters['parent_id'] : 0;

        return $result;
    }

    public function getFilteredLocation()
    {
        if ($this->menuManager->getLocations()) {
            $firstLocation = key($this->menuManager->getLocations());

            return $this->getFilterValue('location', $firstLocation);
        }

        return null;
    }

    /**
     * Получить значение фильтра
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getFilterValue($key, $default = null)
    {
        $data = $this->getFilterParameters();

        return isset($data[$key]) ? $data[$key]['value'] : $default;
    }

    /**
     * Установить значение фильтра и его тип
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param bool $updateDatagrid
     * @return $this
     */
    protected function setFilterValue($key, $value, $type = '', $updateDatagrid = true)
    {
        if ($this->persistFilters && $this->request->query->get('filters') != 'reset') {
            $currentFilterData = parent::getFilterParameters();
            $newFilterData = array_merge($currentFilterData, [$key => ['value' => $value, 'type' => $type]]);
            $this->request->getSession()->set($this->getCode().'.filter.parameters', $newFilterData);

            if ($updateDatagrid) {
                $this->getDatagrid()->setValue($key, '=', $value);
            }
        }
    }

    /**
     * @param Menu $object
     */
    protected function fixObjectProperties($object)
    {
        if ($object->getState()) {
            $object->setState(true);
        }
        if ($object->getIsTest()) {
            $object->setIsTest(true);
        }
        if ($object->getProxyTitle()) {
            $object->setProxyTitle(true);
        }
        if ($object->getProxyLink()) {
            $object->setProxyLink(true);
        }
        $object->setPosition((int)$object->getPosition());
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'location',
                null,
                [
                    'field_type' => 'choice',
                    'field_options' => [
                        'choices' => $this->menuManager->getLocations(),
                        'empty_value' => false,
                    ],
                ]
            );

        if (is_null($this->getFilterValue('location'))) {
            $firstLocation = key($this->menuManager->getLocations());
            $this->setFilterValue('location', $firstLocation);
        }

        if ($this->getFilterValue('parent_id')) {
            $menuItem = $this->getMenuRepository()->find($this->getFilterValue('parent_id'));
            if ($menuItem instanceof Menu && $menuItem->getLocation() != $this->getFilterValue('location')) {
                $this->setFilterValue('parent_id', 0);
            }
        }
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $this->setTemplate('list', 'OctavaMenuBundle:CRUD:menu_list.html.twig');
        $listMapper->remove('batch');

        $treeQuery = $this->getMenuRepository()->createQueryBuilder('m')
            ->where('m.location = :location')
            ->setParameter('location', $this->getFilteredLocation());

        $this->treeManager->setQueryBuilder($treeQuery)
            ->setParentField('parentId')
            ->setUrlParam('filter[parent_id][value]')
            ->setNameField('title')
            ->setOrderString('m.ord')
            ->setActZeroAsNull(true)
            ->setLinkPath($this->generateUrl('list'))
            ->setSelected($this->getFilteredParentId())
            ->setLevelTemplate('OctavaTreeBundle:JsNavigation:level.html.twig')
            ->setTreeTemplate('OctavaTreeBundle:JsNavigation:tree.html.twig')
            ->setSelected(PHP_INT_MAX);

        $listMapper
            ->addIdentifier(
                'title',
                null,
                [
                    'sortable' => false,
                    'template' => 'OctavaMenuBundle:CRUD:menu_list_title_field.html.twig',
                ]
            )
            ->add(
                'link',
                null,
                [
                    'sortable' => false,
                    'template' => 'OctavaMenuBundle:CRUD:menu_list_link_field.html.twig',
                ]
            )
            ->add('position', 'sortable', ['sortable' => false])
            ->add('state', null, ['sortable' => false])
            ->add('isTest', null, ['sortable' => false])
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'edit' => [],
                        'delete' => [],
                        'create' => [
                            'template' => 'OctavaStructureBundle:CRUD:menu_list__action_create.html.twig',
                        ],
                        'history' => [],
                    ],
                ]
            );
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $request = $this->getRequest();

        $isCreateAction = $request->attributes->get('_sonata_name') == 'admin_robo_menu_menu_create';

        $structureRepository = $this
            ->entityManager
            ->getRepository('OctavaStructureBundle:Structure');
        $structureData = $structureRepository->getFlatTreeForSelect(0);

        $parentSelect = $this->getMenuRepository()
            ->getFlatTreeForSelect($this->getFilteredLocation(), $this->getSubject()->getId());

        $parentChoiceParams = [
            'label' => $this->trans('admin.parent_item'),
            'choices' => [$this->trans('admin.select.root_element')] + $parentSelect,
        ];
        if ($isCreateAction && $request->get($this->getIdParameter())) {
            $parentChoiceParams['data'] = $structureRepository->find($request->get($this->getIdParameter()));
        }

        $this->translationMapper
            ->setFormMapper($formMapper)
            ->with()
            ->add(
                'title',
                'menu_related_text',
                [
                    'translatable' => true,
                    'label' => $this->trans('admin.title'),
                    'from_field' => 'title',
                    'proxy_field' => 'proxyTitle',
                    'locale' => '',
                    'structure_value' => $this->getStructureTitles(),
                    'required' => false,
                ]
            )
            ->add('proxyTitle', 'hidden', ['translatable' => true])
            ->add(
                'link',
                'menu_related_text',
                [
                    'translatable' => true,
                    'label' => $this->trans('admin.link'),
                    'required' => false,
                    'from_field' => 'link',
                    'proxy_field' => 'proxyLink',
                    'locale' => '',
                    'structure_value' => $this->getStructurePath(),
                ]
            )
            ->add('proxyLink', 'hidden', ['translatable' => true])
            ->add('ord', 'text', ['label' => $this->trans('admin.order'), 'required' => false])
            ->add(
                'location',
                'choice',
                [
                    'choices' => $this->menuManager->getLocations(),
                ]
            )
            ->add(
                'parent',
                'choice',
                $parentChoiceParams
            )
            ->add(
                'structure',
                'choice',
                [
                    'choices' => $structureData,
                    'label' => $this->trans('admin.structure_relation'),
                    'empty_value' => $this->trans('admin.not_related'),
                    'required' => false,
                ]
            )
            ->add('state', null, ['label' => $this->trans('admin.state'), 'required' => false])
            ->add('isTest', null, ['label' => $this->trans('admin.only_for_test'), 'required' => false])
            ->end();

        $transformerMenu = new ModelToIdTransformer(
            $this->getModelManager(),
            $this->getClass()
        );
        $formMapper->get('parent')->resetViewTransformers()->addViewTransformer($transformerMenu, true);

        $transformerStructure = new ModelToIdTransformer(
            $this->getModelManager(),
            'Robo\\StructureBundle\\Entity\\Structure'
        );
        $formMapper->get('structure')->resetViewTransformers()->addViewTransformer($transformerStructure, true);
    }

    /**
     * @return MenuRepository
     */
    protected function getMenuRepository()
    {
        return $this->entityManager->getRepository($this->getClass());
    }

    /**
     * @return StructureRepository
     */
    protected function getStructureRepository()
    {
        return $this->entityManager->getRepository('OctavaStructureBundle:Structure');
    }

    protected function getStructureTitles()
    {
        $structureTitles = [];
        if (!$this->getSubject()->getStructure()) {
            return $structureTitles;
        }

        $structureRepository = $this->getStructureRepository();

        $relatedStructureItem = null;
        $relatedStructureItem = $structureRepository->find($this->getSubject()->getStructure()->getId());

        if ($relatedStructureItem) {
            /** @var TranslationRepository $translationRepository */
            $translationRepository = $this->entityManager
                ->getRepository('Gedmo\Translatable\Entity\Translation');
            $translations = $translationRepository->findtranslations($relatedStructureItem);
            foreach ($translations as $locale => $translation) {
                $structureTitles[$locale] = !empty($translation['title']) ? $translation['title'] : '';
            }
        }

        return $structureTitles;
    }


    protected function getStructurePath()
    {
        $structurePaths = [];
        if (!$this->getSubject()->getStructure()) {
            return $structurePaths;
        }

        $structureRepository = $this->getStructureRepository();
        $relatedStructureItem = $structureRepository->find($this->getSubject()->getStructure()->getId());

        if ($relatedStructureItem) {
            /** @var TranslationRepository $translationRepository */
            $translationRepository = $this->entityManager
                ->getRepository('Gedmo\Translatable\Entity\Translation');
            $translations = $translationRepository->findtranslations($relatedStructureItem);
            foreach ($translations as $locale => $translation) {
                $structurePaths[$locale] = empty($translation['path']) ?
                    $relatedStructureItem->getPath() :
                    $translation['path'];
            }
        }

        return $structurePaths;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->add(
                'import',
                'import',
                [
                    '_controller' => $collection->getBaseControllerName().':import',
                ]
            )
            ->add(
                'refreshCache',
                'refreshCache',
                [
                    '_controller' => $collection->getBaseControllerName().':refreshCache',
                ]
            );
        $collection->remove('show');
    }
}
