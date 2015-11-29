<?php

namespace Octava\Bundle\AdminMenuBundle\Admin;

use Octava\Bundle\AdminMenuBundle\AdminMenuManager;
use Octava\Bundle\AdminMenuBundle\Dict\Types;
use Octava\Bundle\AdminMenuBundle\Entity\AdminMenu;
use Octava\Bundle\AdminMenuBundle\Entity\AdminMenuRepository;
use Octava\Bundle\AdminMenuBundle\Form\Type\AdminClassChoiceType;
use Octava\Bundle\AdminMenuBundle\Form\Type\EntityType;
use Octava\Bundle\MuiBundle\Form\TranslationMapper;
use Octava\Bundle\TreeBundle\TreeManager;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminMenuAdmin extends Admin
{
    /**
     * @var string
     */
    protected $translationDomain = 'OctavaAdminMenuBundle';

    /**
     * @var Types
     */
    protected $dictTypes;

    /**
     * @var AdminMenuManager
     */
    protected $adminMenuManager;

    /**
     * @var TreeManager
     */
    protected $treeManager;

    /**
     * @var TranslationMapper
     */
    protected $translationMapper;

    public function configure()
    {
        $container = $this->getConfigurationPool()
            ->getContainer();

        $this->adminMenuManager = $container->get('octava_admin_menu.admin_menu_manager');
        $this->dictTypes = $container->get('octava_admin_menu.dict.types');
        $this->treeManager = $container->get('octava_tree.tree_manager');
        $this->translationMapper = $container->get('octava_mui.form.translation_mapper');
    }

    /**
     * @return Types
     */
    public function getDictTypes()
    {
        return $this->dictTypes;
    }

    /**
     * @return AdminMenuManager
     */
    public function getAdminMenuManager()
    {
        return $this->adminMenuManager;
    }

    /**
     * @return TreeManager
     */
    public function getTreeManager()
    {
        return $this->treeManager;
    }

    public function getMenuTree()
    {
        $ret = $this->getAdminMenuManager()->getTree();

        return $ret;
    }

    /**
     * @return TranslationMapper
     */
    public function getTranslationMapper()
    {
        return $this->translationMapper;
    }

    public function validate(ErrorElement $errorElement, $object)
    {
        /** @var AdminMenu $object */
        if ($object->getType() == AdminMenu::TYPE_MODULE) {
            $errorElement->with('adminClass')
                ->addConstraint(new NotBlank())
                ->end();
        }
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $this->setTemplate('list', 'OctavaAdminMenuBundle:CRUD:admin_menu_list.html.twig');

        $listMapper
            ->add(
                'title',
                null,
                [
                    'sortable' => false,
                    'code' => null,
                    'template' => 'OctavaAdminMenuBundle:CRUD:admin_menu_list_title_field.html.twig',
                ]
            )
            ->add(
                'position',
                null,
                [
                    'sortable' => false,
                ]
            )
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'show' => [],
                        'edit' => [],
                        'delete' => [],
                        'create' => [
                            'template' => 'OctavaAdminMenuBundle:CRUD:admin_menu_list__action_create.html.twig',
                        ],
                    ],
                ]
            )
            ->remove('batch');

//        $treeQuery = $this->getRepository()
//            ->createQueryBuilder('m')
//            ->where('m.type = :type')
//            ->setParameter('type', AdminMenu::TYPE_FOLDER);
//
//        $this->getTreeManager()->setQueryBuilder($treeQuery)
//            ->setParentField('parent')
//            ->setNameField('title')
//            ->setOrderString('m.position')
//            ->setActZeroAsNull(true)
//            ->setLinkPath($this->generateUrl('list'))
//            ->setLevelTemplate('OctavaTreeBundle:JsNavigation:level.html.twig')
//            ->setTreeTemplate('OctavaTreeBundle:JsNavigation:tree.html.twig')
//            ->setSelected(PHP_INT_MAX);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $id = $this->getRequest()->get($this->getIdParameter());
        $parent = null;
        $excludedIds = [];
        if (!empty($id)) {
            if ($this->getRequest()->get('_route') != 'admin_octava_adminmenu_adminmenu_create') {
                $excludedIds[] = $id;
            } else {
                $parent = $this->getRepository()->find($id);
            }
        }

        $this->setTemplate('edit', 'OctavaAdminMenuBundle:CRUD:admin_menu_edit.html.twig');

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
                'parent',
                EntityType::TYPE_NAME,
                [
                    'class' => $this->getClass(),
                    'empty_value' => '',
                    'required' => false,
                    'excluded_ids' => $excludedIds,
                    'default_data' => $parent,
                ]
            )
            ->add(
                'type',
                'choice',
                [
                    'choices' => $this->getDictTypes()->getChoices(),
                    'empty_value' => '',
                    'attr' => [
                        'data-type-select' => '1',
                    ],
                ]
            )
            ->add(
                'adminClass',
                AdminClassChoiceType::TYPE_NAME,
                [
                    'empty_value' => '',
                    'attr' => [
                        'class' => 'chzn-select',
                        'data-class-select' => '1',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'position',
                'number',
                [
                    'required' => false,
                ]
            )
            ->end();
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
            ->add('type')
            ->add('adminClass')
            ->add('position');
    }

    /**
     * @return AdminMenuRepository
     */
    protected function getRepository()
    {
        return $this->getConfigurationPool()
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository($this->getClass());
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
        $collection->remove('batch');
        $collection->remove('export');
    }
}
