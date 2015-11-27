<?php

namespace Octava\Bundle\AdministratorBundle\Admin;

use Doctrine\ORM\QueryBuilder;
use FOS\UserBundle\Doctrine\UserManager;
use Octava\Bundle\AdministratorBundle\Config\AdministratorConfig;
use Octava\Bundle\AdministratorBundle\Entity\Administrator;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdministratorAdmin extends Admin
{
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var bool
     */
    protected $showHidden = false;

    /**
     * @return UserManager
     */
    public function getUserManager()
    {
        return $this->userManager;
    }

    /**
     * @param UserManager $userManager
     * @return self
     */
    public function setUserManager($userManager)
    {
        $this->userManager = $userManager;
        return $this;
    }

    /**
     * @param string $context
     * @return QueryBuilder
     */
    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = parent::createQuery($context);

        $whiteList = $this->getWhiteList();
        if (!$this->getUser()->getShowHidden() && !empty($whiteList)) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('o.username', $whiteList));
        }

        return $queryBuilder;
    }

    /**
     * @return array
     */
    public function getWhiteList()
    {
        $result = $this->getConfigurationPool()
            ->getContainer()
            ->get('octava_administrator.config.administrator')
            ->getWhiteList();

        return $result;
    }

    public function prePersist($object)
    {
        $salt = md5(microtime(true));
        /** @var AdministratorConfig $config */
        $config = $this->getConfigurationPool()->getContainer()
            ->get('octava_administrator.config.administrator');
        /** @var Administrator $object */
        $object->setSalt($salt);
        $object->setShowHidden($config->getDefaultShowHidden());
        $this->userManager->updatePassword($object);
    }

    public function preUpdate($object)
    {
        $this->userManager->updatePassword($object);
    }

    public function validate(ErrorElement $errorElement, $object)
    {
        /** @var Administrator $object */
        if (!$object->getId()) {
            $errorElement->with('plainPassword')->addConstraint(new NotBlank())->end();
        }
    }

    /**
     * @return Administrator
     */
    public function getUser()
    {
        return $this->getConfigurationPool()->getContainer()
            ->get('security.token_storage')->getToken()->getUser();
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('username')
            ->add('email')
            ->add('enabled')
            ->add('groups')
            ->add('showHidden');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('username')
            ->add('email')
            ->add('lastLogin')
            ->add('updatedAt')
            ->add('enabled')
            ->add('showHidden')
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $selected = [];
        if ($this->getRequest()->get($this->getIdParameter())) {
            /** @var Administrator $object */
            $object = $this->getObject($this->getRequest()->get($this->getIdParameter()));
            $selected = array_keys($object->getGroupResources());
        }

        $formMapper
            ->with($this->trans('admin.tab.administrator'))
            ->add('username')
            ->add('email')
            ->add('enabled')
            ->add('plainPassword', 'text', ['required' => false])
            ->end()
            ->with($this->trans('admin.tab.group'))
            ->add('groups', null, ['expanded' => true, 'multiple' => true])
            ->end()
            ->with($this->trans('admin.tab.acl'))
            ->add('resources', 'acl_resources', ['required' => false, 'selectedCell' => $selected])
            ->end()
            ->with($this->trans('admin.tab.locale'))
            ->add('locales', null, ['expanded' => true, 'multiple' => true])
            ->end();
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('username')
            ->add('email')
            ->add('enabled')
            ->add('salt')
            ->add('password')
            ->add('lastLogin')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('showHidden');
    }
}
