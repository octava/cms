<?php

namespace Octava\Bundle\MenuBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class MenuAdmin extends Admin
{
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
            ->add('proxyTitle')
            ->add('link')
            ->add('proxyLink')
            ->add('location')
            ->add('position')
            ->add('state')
            ->add('isTest');
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
            ->add('proxyTitle')
            ->add('link')
            ->add('proxyLink')
            ->add('location')
            ->add('position')
            ->add('state')
            ->add('isTest')
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
            ->add('proxyTitle')
            ->add('link')
            ->add('proxyLink')
            ->add('location')
            ->add('position')
            ->add('state')
            ->add('isTest');
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
            ->add('proxyTitle')
            ->add('link')
            ->add('proxyLink')
            ->add('location')
            ->add('position')
            ->add('state')
            ->add('isTest');
    }
}
