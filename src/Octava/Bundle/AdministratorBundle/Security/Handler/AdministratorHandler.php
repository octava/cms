<?php
namespace Octava\Bundle\AdministratorBundle\Security\Handler;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\AdministratorBundle\Entity\Administrator;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class AdministratorHandler implements SecurityHandlerInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @param EntityManager $entityManager
     * @param TokenStorage $tokenStorage
     */
    public function __construct(EntityManager $entityManager, TokenStorage $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param string|array $attributes
     * @param null $object
     *
     * @return boolean
     */
    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        /** @var Administrator $administrator */
        $administrator = $this->tokenStorage->getToken()->getUser();
        if (!$administrator instanceof Administrator) {
            return false;
        }

        $availableResources = $administrator->getAvailableResources();
        $list = $this->entityManager->getRepository('OctavaAdministratorBundle:Resource')->getList();

        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }

        foreach ($attributes as $attribute) {
            if (in_array($attribute, ["EXPORT", "VIEW"])) {
                $attribute = 'LIST';
            }

            if (!isset($list[get_class($admin)][$attribute])) {
                continue;
            }

            $currentId = $list[get_class($admin)][$attribute];
            if (isset($availableResources[$currentId])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a sprintf template to get the role
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     *
     * @return string
     */
    public function getBaseRole(AdminInterface $admin)
    {
        // TODO: Implement getBaseRole() method.
    }

    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     */
    public function buildSecurityInformation(AdminInterface $admin)
    {
        // TODO: Implement buildSecurityInformation() method.
    }

    /**
     * Create object security, fe. make the current user owner of the object
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param mixed $object
     *
     * @return void
     */
    public function createObjectSecurity(AdminInterface $admin, $object)
    {
        // TODO: Implement createObjectSecurity() method.
    }

    /**
     * Remove object security
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param mixed $object
     *
     * @return void
     */
    public function deleteObjectSecurity(AdminInterface $admin, $object)
    {
        // TODO: Implement deleteObjectSecurity() method.
    }
}
