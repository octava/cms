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
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    public function preUpdate($object)
    {
        $this->prePersist($object);
    }

    /**
     * @param Structure $object
     * @return void
     */
    public function prePersist($object)
    {
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

    protected function getFilteredParentId()
    {
        $parameters = $this->getFilterParameters();
        $result = isset($parameters['parent_id']) ? $parameters['parent_id'] : 0;

        return $result;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('title')
            ->add('description')
            ->add('type')
            ->add('alias')
            ->add('path')
            ->add('state')
            ->add('template')
            ->add('routeName');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('title')
            ->add('description')
            ->add('type')
            ->add('alias')
            ->add('path')
            ->add('state')
            ->add('template')
            ->add('routeName')
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'show' => [],
                        'edit' => [],
                        'delete' => [],
                    ],
                ]
            );
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->setTemplate('edit', 'RoboStructureBundle:CRUD:edit.html.twig');

        $request = $this->getRequest();
        $isCreateAction = $request->attributes->get('_sonata_name') == 'admin_octava_structure_structure_create';
        $parentSelect = $this->getRepository()->getFlatTreeForSelect($this->getSubject()->getId());
        $parentChoiceParams = [
            'label' => $this->trans('admin.parent_item'),
            'choices' => [$this->trans('admin.select.root_element')] + $parentSelect,
        ];
        if ($isCreateAction) {
            $parentChoiceParams['data'] = $request->get($this->getIdParameter());
        }

        $formMapper
            ->add('title')
            ->add('description')
            ->add('type')
            ->add('alias')
            ->add('path')
            ->add('state')
            ->add('template')
            ->add('routeName');
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('title')
            ->add('description')
            ->add('type')
            ->add('alias')
            ->add('path')
            ->add('state')
            ->add('template')
            ->add('routeName');
    }

    /**
     * @return StructureRepository
     */
    protected function getRepository()
    {
        return $this->getEntityManager()->getRepository($this->getClass());
    }
}
