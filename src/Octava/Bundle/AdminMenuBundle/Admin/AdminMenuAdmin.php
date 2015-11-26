<?php

namespace Octava\Bundle\AdminMenuBundle\Admin;

use Octava\Bundle\AdminMenuBundle\AdminMenuManager;
use Octava\Bundle\AdminMenuBundle\Dict\Types;
use Octava\Bundle\AdminMenuBundle\Entity\AdminMenu;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminMenuAdmin extends Admin
{
    /**
     * @var Types
     */
    protected $dictTypes;

    /**
     * @var AdminMenuManager
     */
    protected $adminMenuManager;

    public function configure()
    {
        $container = $this->getConfigurationPool()
            ->getContainer();

        $this->adminMenuManager = $container
            ->get('octava_admin_menu.admin_menu_manager');
        $this->dictTypes = $container->get('octava_admin_menu.dict.types');
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
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('title')
            ->add('type')
            ->add('adminClass')
            ->add('position');
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
            ->add('type')
            ->add('adminClass')
            ->add('position')
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
        $formMapper
            ->add('id')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('title')
            ->add('type')
            ->add('adminClass')
            ->add('position');
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
}
