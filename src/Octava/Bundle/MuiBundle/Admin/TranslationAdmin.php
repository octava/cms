<?php

namespace Octava\Bundle\MuiBundle\Admin;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Octava\Bundle\MuiBundle\TranslationManager;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TranslationAdmin extends Admin
{
    const FILTER_LOCATION_FRONTEND = 'frontend';
    const FILTER_LOCATION_BACKEND = 'backend';

    /**
     * @var string
     */
    protected $translationDomain = 'OctavaMuiBundle';

    /**
     * @var int
     */
    protected $maxPerPage = 50;

    /**
     * @var array
     */
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 50,
        '_sort_order' => 'ASC',
        '_sort_by' => 'source',
        'location' => [
            'value' => self::FILTER_LOCATION_FRONTEND,
        ],
    ];

    /**
     * @var TranslationManager
     */
    protected $translationManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->entityManager = $this->getConfigurationPool()
                ->getContainer()->get('doctrine.orm.entity_manager');
        }

        return $this->entityManager;
    }

    /**
     * @return TranslationManager
     */
    public function getTranslationManager()
    {
        if (null === $this->translationManager) {
            $this->translationManager = $this->getConfigurationPool()
                ->getContainer()->get('octava_mui.translation_manager');
        }

        return $this->translationManager;
    }

    public function postUpdate($object)
    {
        $this->getTranslationManager()->clearCache();
    }

    public function postPersist($object)
    {
        $this->getTranslationManager()->clearCache();
    }

    public function postRemove($object)
    {
        $this->getTranslationManager()->clearCache();
    }

    public function getTree()
    {
        $entityManger = $this->getEntityManager();
        $treeManager = $this->getConfigurationPool()
            ->getContainer()->get('octava_tree.tree_manager');

        $repository = $entityManger->getRepository('OctavaMuiBundle:Translation');
        $parameters = $this->getFilterParameters();
        $selected = isset($parameters['domain']) ? $parameters['domain'] : 0;
        $result = $treeManager->setQueryBuilder($repository->getDomainsQueryBuilder())
            ->setUrlParam('filter[domain]')
            ->setNameField('domain')
            ->setLinkPath($this->generateUrl('list'))
            ->setPrimaryField('domain')
            ->setSelected($selected);

        return $result;
    }

    public function createQuery($context = 'list')
    {
        /** @var ProxyQuery|QueryBuilder $query */
        $query = parent::createQuery($context);

        if ($context == 'list') {
            $parameters = $this->getFilterParameters();
            $domainValue = isset($parameters['domain']) ? $parameters['domain'] : false;
            list($tableAlias) = $query->getQueryBuilder()->getRootAliases();
            if ($domainValue) {
                $query->where($tableAlias.'.domain = :domain')
                    ->setParameter('domain', $domainValue);
            }
            $emptyLocale = !empty($parameters['emptyLocale']['value']) ? $parameters['emptyLocale']['value'] : false;
            if ($emptyLocale) {
                $query->andWhere($tableAlias.'.translations NOT LIKE :emptyLocale')
                    ->setParameter('emptyLocale', '%s:2:"'.$emptyLocale.'"%');
            }

            $location = !empty($parameters['location']['value']) ? $parameters['location']['value'] : false;
            if ($location) {
                if ($location == self::FILTER_LOCATION_FRONTEND) {
                    $query->andWhere($tableAlias.'.source NOT LIKE :excludeAdmin')
                        ->setParameter('excludeAdmin', '%admin\.%');
                } elseif ($location == self::FILTER_LOCATION_BACKEND) {
                    $query->andWhere($tableAlias.'.source LIKE :excludeAdmin')
                        ->setParameter('excludeAdmin', '%admin\.%');
                }
            }
        }

        return $query;
    }

    public function preUpdate($object)
    {
        $oldData = $this->getEntityManager()
            ->getUnitOfWork()->getOriginalEntityData($object);

        $newTranslations = $object->getTranslations();
        foreach ($oldData['translations'] as $locale => $value) {
            if (!isset($newTranslations[$locale])) {
                $newTranslations[$locale] = $value;
            }
        }

        $object->setTranslations($newTranslations);
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('source')
            ->add(
                'translations',
                null,
                ['label' => 'admin.filter.translations'],
                'text',
                []
            )
            ->add(
                'emptyLocale',
                'doctrine_orm_choice',
                [
                    'label' => 'admin.filter.empty_value',
                ],
                'choice',
                [
                    'choices' => $this->getEntityManager()
                        ->getRepository('OctavaMuiBundle:Locale')->findForChoices(),
                    'mapped' => false,
                    'empty_value' => '---',
                    'empty_data' => null,
                    'multiple' => true,
                ]
            )
            ->add(
                'locales',
                'doctrine_orm_choice',
                [
                    'label' => 'admin.filter.show_locale_value',
                ],
                'choice',
                [
                    'choices' => $this->getEntityManager()
                        ->getRepository('OctavaMuiBundle:Locale')->findForChoices(['ru', 'en']),
                    'mapped' => false,
                    'empty_value' => '---',
                    'empty_data' => null,
                ]
            )
            ->add(
                'location',
                'doctrine_orm_choice',
                [
                    'label' => 'admin.filter.location',
                ],
                'choice',
                [
                    'choices' => [
                        self::FILTER_LOCATION_FRONTEND => 'admin.filter.location.frontend',
                        self::FILTER_LOCATION_BACKEND => 'admin.filter.location.backend',
                    ],
                    'mapped' => false,
                    'empty_value' => '--',
                    'empty_data' => null,
                    'translation_domain' => 'OctavaMuiBundle',
                    'multiple' => true,
                ]
            );
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $this->setTemplate('list', 'OctavaMuiBundle:CRUD:translation_list.html.twig');
        $listMapper
            ->add('id')
            ->add('domain')
            ->add('source')
            ->add(
                'enLang',
                TextType::class,
                [
                    'mapped' => false,
                    'template' => 'OctavaMuiBundle:CRUD:translation_list_add_land.html.twig',
                    'label' => 'EN',
                    'add_lang' => 'en',
                ]
            )->add(
                'ruLang',
                TextType::class,
                [
                    'mapped' => false,
                    'template' => 'OctavaMuiBundle:CRUD:translation_list_add_land.html.twig',
                    'label' => 'RU',
                    'add_lang' => 'ru',
                ]
            );
        $filterParameters = $this->getFilterParameters();
        if (!empty($filterParameters['locales']['value'])) {
            $listMapper->add(
                'addLang',
                TextType::class,
                [
                    'mapped' => false,
                    'template' => 'OctavaMuiBundle:CRUD:translation_list_add_land.html.twig',
                    'label' => strtoupper($filterParameters['locales']['value']),
                    'add_lang' => $filterParameters['locales']['value'],
                ]
            );
        }

        $listMapper->add(
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
            ->add('domain')
            ->add('source')
            ->add('translations');
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
            ->add('domain')
            ->add('source')
            ->add('translations');
    }
}
