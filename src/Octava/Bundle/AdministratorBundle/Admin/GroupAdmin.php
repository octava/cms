<?php

namespace Octava\Bundle\AdministratorBundle\Admin;

use Octava\Bundle\AdministratorBundle\Form\Type\ResourcesType;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class GroupAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('name')
            ->add('createdAt')
            ->add('updatedAt');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('name')
            ->add('createdAt')
            ->add('updatedAt')
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
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
            ->with($this->trans('form.label_groups'))
            ->add('name')
            ->end()
            ->with('ACL')
            ->add('resources', ResourcesType::TYPE_NAME, ['required' => false])
            ->end();
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('name')
            ->add('createdAt')
            ->add('updatedAt');
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
    }
}
