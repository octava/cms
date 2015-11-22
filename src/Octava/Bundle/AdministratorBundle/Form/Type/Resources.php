<?php
namespace Octava\Bundle\AdministratorBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\AdministratorBundle\Config\AdministratorConfig;
use Octava\Bundle\AdministratorBundle\Entity\Administrator;
use Octava\Bundle\AdministratorBundle\Entity\Resource as EntityResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\TranslatorInterface;

class Resources extends AbstractType
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var AdministratorConfig
     */
    protected $config;

    public function __construct(
        EntityManager $entityManager,
        TranslatorInterface $translator,
        TokenStorage $tokenStorage,
        AdministratorConfig $settingsManager
    ) {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->config = $settingsManager;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @return TokenStorage
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    /**
     * @return AdministratorConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'acl_resources';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => 'Octava\\Bundle\\AdministratorBundle\\Entity\\Resource',
            'multiple' => true,
            'selectedCell' => []
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $repository = $this->getEntityManager()
            ->getRepository('OctavaAdministratorBundle:Resource');

        /** @var Administrator $user */
        $user = $this->getTokenStorage()->getToken()->getUser();

        if (!$user->getShowHidden()) {
            $visibleModules = $this->getConfig()->getWhiteList();
        } else {
            $visibleModules = null;
        }

        /** @var EntityResource[] $rows */
        $rows = $repository->findBy([], ['sort' => 'ASC']);
        $choices = [];
        foreach ($rows as $row) {
            if (!is_null($visibleModules) && !in_array($row->getResource(), $visibleModules)) {
                continue;
            }
            $a = explode('\\', $row->getResource());
            $domain = $a[0] . $a[1];
            $label = $this->getTranslator()->trans($row->getLabel(), [], $domain);
            $group = $this->getTranslator()->trans($row->getGroupLabel(), [], $row->getGroupLabelDomain());
            $choices[$group][$label][$row->getAction()] = $row;
        }

        $actions = $repository->getActions();
        $view->vars['acl_data'] = $choices;
        $view->vars['acl_actions'] = $actions;
        $view->vars['selectedCell'] = $options['selectedCell'];
    }

    public function getParent()
    {
        return 'entity';
    }
}
