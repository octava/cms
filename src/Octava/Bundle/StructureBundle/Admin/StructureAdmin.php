<?php

namespace Octava\Bundle\StructureBundle\Admin;

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Octava\Bundle\MuiBundle\Form\TranslationMapper;
use Octava\Bundle\StructureBundle\Config\StructureConfig;
use Octava\Bundle\StructureBundle\Entity\Structure;
use Octava\Bundle\StructureBundle\Entity\StructureRepository;
use Octava\Bundle\StructureBundle\Event\ItemUpdateEvent;
use Octava\Bundle\StructureBundle\StructureEvents;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;

class StructureAdmin extends Admin
{
    /**
     * @var Router
     */
    public $router;
    /**
     * @var string
     */
    protected $translationDomain = 'OctavaStructureBundle';
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var StructureConfig
     */
    protected $structureConfig;

    /**
     * @var FilesystemCache
     */
    protected $structureCache;

    /**
     * @var TranslationMapper
     */
    protected $translationMapper;

    /**
     * @var FilesystemCache
     */
    protected $menuCache;

    /**
     * @return TranslationMapper
     */
    public function getTranslationMapper()
    {
        return $this->translationMapper;
    }

    public function configure()
    {
        $container = $this->getConfigurationPool()
            ->getContainer();

        $this->router = $container->get('router');
        $this->menuCache = $container->get('octava_menu.cache');
        $this->structureCache = $container->get('octava_structure.cache');
        $this->translationMapper = $container->get('octava_mui.form.translation_mapper');
        $this->dispatcher = $container->get('event_dispatcher');
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->structureConfig = $container->get('octava_structure.config.structure_config');
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return FilesystemCache
     */
    public function getMenuCache()
    {
        return $this->menuCache;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return StructureConfig
     */
    public function getStructureConfig()
    {
        return $this->structureConfig;
    }

    /**
     * @return FilesystemCache
     */
    public function getStructureCache()
    {
        return $this->structureCache;
    }

    public function getNewInstance()
    {
        $object = parent::getNewInstance();

        if ($this->getRequest()->getMethod() !== 'POST') {
            if ($parentId = $this->getFilteredParentId()) {
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
     * @param Structure $object
     * @return void
     */
    public function preUpdate($object)
    {
        $object->preUpdate();

        $this->getConfigurationPool()->getContainer()
            ->get('octava_structure.structure_manager')
            ->update($object->getId());
    }

    /**
     * @param Structure $object
     * @return void
     */
    public function prePersist($object)
    {
        $object->prePersist();
        if ($object->getState()) {
            $object->setState(true);
        }
    }

    public function postUpdate($object)
    {
        $this->postPersist($object);

        $this->getConfigurationPool()->getContainer()
            ->get('octava_structure.structure_manager')
            ->update($object->getId());

        $event = new ItemUpdateEvent($object);
        $this->getDispatcher()->dispatch(StructureEvents::ITEM_UPDATE, $event);

        $this->getEntityManager()->clear($this->getClass());

        $this->getMenuCache()->deleteAll();
        $this->getStructureCache()->deleteAll();
    }

    public function postPersist($object)
    {
        $router = $this->getRouter();
        $router->getMatcher();
        $router->getGenerator();

        $matcherFile = $router->getOption('cache_dir').'/'
            .$router->getOption('matcher_cache_class').'.php';
        $generatorFile = $router->getOption('cache_dir').'/'
            .$router->getOption('generator_cache_class').'.php';

        if (is_file($matcherFile)) {
            unlink($matcherFile);
        }
        if (is_file($generatorFile)) {
            unlink($generatorFile);
        }

        /**
         * @var StructureRepository $repository
         */
        $repository = $this->getEntityManager()->getRepository($this->getClass());
        $repository->updatePath($object);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear($this->getClass());

        $this->getMenuCache()->deleteAll();
        $this->getStructureCache()->deleteAll();
    }


    public function getStructureTree()
    {
        return $this->getEntityManager()
            ->getRepository('OctavaStructureBundle:Structure')->getFlatTree();
    }

    public function getAvailableStates(Structure $structure)
    {
        $ret = [];
        $translations = $this->entityManager
            ->getRepository('OctavaStructureBundle:Structure')
            ->getTranslations($structure);
        foreach ($translations as $locale => $translation) {
            if (!empty($translation['state'])) {
                $ret[] = $locale;
            }
        }

        return implode(', ', $ret);
    }

    public function getStructureUrls(Structure $object)
    {
        $urls = [];
        $container = $this->configurationPool->getContainer();
        $translations = $this->getEntityManager()
            ->getRepository('GedmoTranslatable:Translation')->findTranslations($object);
        foreach ($container->get('octava_mui.office_manager')->getRoutingOffices() as $alias => $office) {
            $url = empty($translations[$alias]['path']) ? $object->getPath() : $translations[$alias]['path'];
            $title = $alias;
            $found = false;
            if (!empty($translations[$alias]['state']) && $object->getType() != $object::TYPE_STRUCTURE_EMPTY) {
                try {
                    $url = $container->get('router')->generate(
                        $object->getRouteName(),
                        ['_locale' => $alias, '_catch_exception' => 1]
                    );
                    if (!empty($url)) {
                        $found = true;
                    }
                } catch (RouteNotFoundException $e) {
                }
            }
            $urls[] = [
                'href' => $url,
                'found' => $found,
                'title' => $title,
            ];
        }

        return $urls;
    }

    protected function getFilteredParentId()
    {
        $parameters = $this->getFilterParameters();
        $result = isset($parameters['parent_id']) ? $parameters['parent_id'] : 0;

        return $result;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $this->setTemplate('list', 'OctavaStructureBundle:CRUD:structure_list.html.twig');

        $listMapper
            ->add(
                'title',
                null,
                [
                    'template' => 'OctavaStructureBundle:CRUD:structure_list_title_field.html.twig',
                    'sortable' => false,
                ]
            )
            ->add(
                'type',
                null,
                [
                    'sortable' => false,
                    'template' => 'OctavaStructureBundle:CRUD:structure_list_type_field.html.twig',
                ]
            )
            ->add(
                'path',
                null,
                [
                    'template' => 'OctavaStructureBundle:CRUD:structure_list_path_field.html.twig',
                    'sortable' => false,
                ]
            )
            ->add(
                'state',
                null,
                [
                    'template' => 'OctavaStructureBundle:CRUD:structure_list_state_field.html.twig',
                    'sortable' => false,
                ]
            )
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'create' => [
                            'template' => 'OctavaStructureBundle:CRUD:structure_list__action_create.html.twig',
                        ],
                        'edit' => [],
                        'delete' => [],
                    ],
                ]
            )
            ->remove('batch');
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->setTemplate('edit', 'OctavaStructureBundle:CRUD:structure_edit.html.twig');

        $request = $this->getRequest();
        $isCreateAction = $request->attributes->get('_sonata_name') == 'admin_octava_structure_structure_create';
        $parentSelect = $this->getRepository()->getFlatTreeForSelect($this->getSubject()->getId());
        $parentChoiceParams = [
            'label' => $this->trans('admin.parent_item'),
            'choices' => [$this->trans('admin.select.root_element')] + $parentSelect,
        ];
        if ($isCreateAction && $request->get($this->getIdParameter())) {
            $parentChoiceParams['data'] = $this->getRepository()
                ->find($request->get($this->getIdParameter()));
        }

        $this->getTranslationMapper()
            ->setFormMapper($formMapper)
            ->with()
            ->add(
                'title',
                'text',
                [
                    'translatable' => true,
                ]
            )
            ->add(
                'alias',
                'text',
                [
                    'translatable' => true,
                ]
            )
            ->add(
                'type',
                'choice',
                [
                    'choices' => $this->getContentTypes(),
                ]
            )
            ->add(
                'routeName',
                null,
                ['required' => false]
            )
            ->add('template', 'choice', ['choices' => $this->getStructureTemplates(), 'required' => false])
            ->add(
                'parent',
                'choice',
                $parentChoiceParams
            )
            ->add(
                'description',
                'ckeditor',
                [
                    'translatable' => true,
                    'required' => false,
                    'config' => ['allowedContent' => true],
                ]
            )
            ->add(
                'state',
                'checkbox',
                [
                    'required' => false,
                    'translatable' => true,
                    'attr' => ['data-addHidden' => 0],
                ]
            )
            ->end();

        $transformerStructure = new ModelToIdTransformer(
            $this->getModelManager(),
            $this->getClass()
        );
        $formMapper->get('parent')->resetViewTransformers()
            ->addViewTransformer($transformerStructure, true);
    }

    /**
     * @return StructureRepository
     */
    protected function getRepository()
    {
        return $this->getEntityManager()->getRepository($this->getClass());
    }

    /**
     * Получить список типов модулей которые можно привязть к узлу
     * @return array
     */
    protected function getContentTypes()
    {
        $types = [
            Structure::TYPE_PAGE => $this->trans('admin.page_type'),
            Structure::TYPE_STRUCTURE_EMPTY => $this->trans('admin.robo_structure_empty'),
        ];
        foreach ($this->getRouter()->getRouteCollection() as $route) {
            /** @var Route $route */
            if ($route->hasDefault('_structure_type')
                && $route->getDefault('_structure_type') != Structure::TYPE_PAGE
            ) {
                $type = $route->getDefault('_structure_type');
                $types[$type] = $this->trans($type);
            }
        }

        return $types;
    }

    protected function getStructureTemplates()
    {
        $ret = [
            '' => $this->trans('admin.default_template'),
        ];
        foreach ($this->structureConfig->getAdditionalTemplates() as $name => $template) {
            $ret[$template] = $this->trans(sprintf('admin.%s', $name));
        }

        return $ret;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add(
            'refreshCache',
            'refreshCache',
            [
                '_controller' => $collection->getBaseControllerName().':refreshCache',
            ]
        );
        $collection->remove('show');
        $collection->remove('batch');
        $collection->remove('export');
    }
}
