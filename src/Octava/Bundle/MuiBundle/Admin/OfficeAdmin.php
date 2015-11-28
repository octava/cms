<?php

namespace Octava\Bundle\MuiBundle\Admin;

use Octava\Bundle\MuiBundle\Entity\Office;
use Octava\Bundle\MuiBundle\Form\Type\AvailableCurrencies;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class OfficeAdmin
 * @package Octava\Bundle\MuiBundle\Admin
 *
 */
class OfficeAdmin extends Admin
{
    protected $translationDomain = 'OctavaMuiBundle';

    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
            ->with('defaultLanguage')
            ->addConstraint(new Callback([$this, 'validateDefaultLanguage']))
            ->end()
            ->with('includeLangInUrl')
            ->addConstraint(new Callback([$this, 'validateIncludeLangUrl']))
            ->end();
    }

    public function validateDefaultLanguage($lang, ExecutionContextInterface $context)
    {
        $entityManager = $this->getConfigurationPool()
            ->getContainer()->get('doctrine.orm.entity_manager');

        /** @var Office $object */
        $object = $this->getSubject();

        /** @var Form $form */
        $form = $context->getRoot();

        $host = $form->get('host')->getData();

        /** @var Office[] $offices */
        $offices = $entityManager->getRepository('OctavaMuiBundle:Office')
            ->getOfficesByLocaleHost($lang, $host, $object->getId());

        if (!empty($offices[0])) {
            $context->buildViolation(
                $this->trans(
                    'admin.error.office_locale_host_exists',
                    [
                        '{locale}' => $lang,
                        '{host}' => $host,
                        '{alias}' => $offices[0]->getAlias(),
                    ]
                )
            )->addViolation();
        }
    }

    public function validateIncludeLangUrl($val, ExecutionContextInterface $context)
    {
        if ($val) {
            return;
        }
        $entityManager = $this->getConfigurationPool()
            ->getContainer()->get('doctrine.orm.entity_manager');

        /** @var Office $object */
        $object = $this->getSubject();

        /** @var Form $form */
        $form = $context->getRoot();

        $host = $form->get('host')->getData();

        /** @var Office[] $offices */
        $offices = $entityManager->getRepository('OctavaMuiBundle:Office')
            ->getOfficesByHost($host, $object->getId());

        foreach ($offices as $office) {
            if (!$office->getIncludeLangInUrl()) {
                $context->buildViolation(
                    $this->trans(
                        'admin.error.office_host_exists',
                        ['{host}' => $host, '{alias}' => $office->getAlias()]
                    )
                )->addViolation();
                break;
            }
        }
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('clearCache');
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('name')
            ->add('alias')
            ->add('email')
            ->add('protocol')
            ->add('host');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $this->setTemplate('list', 'OctavaMuiBundle:CRUD:office_list.html.twig');

        $listMapper
            ->add('id')
            ->add('updatedAt')
            ->add('name')
            ->add('alias')
            ->add('protocol')
            ->add('host')
            ->add('relatedUrl')
            ->add('defaultLanguage')
            ->add('availableLanguages')
            ->add('currencies')
            ->add('includeLangInUrl')
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
        $languages = $this->getLocales();
        $formMapper
            ->add('id')
            ->add('name')
            ->add('alias')
            ->add('email')
            ->add(
                'protocol',
                'choice',
                [
                    'choices' => ['http://' => 'HTTP', 'https://' => 'HTTPS'],
                ]
            )
            ->add('host')
            ->add('relatedUrl', null, ['required' => false])
            ->add(
                'defaultLanguage',
                'choice',
                [
                    'choices' => $languages,
                ]
            )
            ->add(
                'recognizeLanguage',
                'choice',
                [
                    'choices' => $languages,
                    'empty_value' => $this->trans('admin.recognize_language_disabled'),
                    'required' => false,
                ]
            )
            ->add(
                'availableLanguages',
                'choice',
                [
                    'choices' => $this->getLocales(),
                    'multiple' => true,
                ]
            )
            ->add(
                'currencies',
                AvailableCurrencies::TYPE_NAME,
                ['required' => false]
            )
            ->add('includeLangInUrl')
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
            ->add('name')
            ->add('alias')
            ->add('email')
            ->add('protocol')
            ->add('host')
            ->add('relatedUrl')
            ->add('defaultLanguage')
            ->add('recognizeLanguage')
            ->add('availableLanguages')
            ->add('currencies')
            ->add('includeLangInUrl')
            ->add('position');
    }

    protected function getLocales()
    {
        $list = $this->getConfigurationPool()
            ->getContainer()
            ->get('octava_mui.locale_manager')
            ->getAll();

        $result = [];
        foreach ($list as $item) {
            $result[$item->getAlias()] = $item->getName().' ('.$item->getAlias().')';
        }

        return $result;
    }
}
