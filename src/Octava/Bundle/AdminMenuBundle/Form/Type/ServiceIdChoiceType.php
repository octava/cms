<?php
namespace Octava\Bundle\AdminMenuBundle\Form\Type;

use Octava\Bundle\AdminMenuBundle\AdminMenuManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceIdChoiceType extends AbstractType
{
    /**
     * @var AdminMenuManager
     */
    protected $menuManager;

    public function __construct(AdminMenuManager $menuManager)
    {
        $this->menuManager = $menuManager;
    }

    /**
     * @return AdminMenuManager
     */
    public function getMenuManager()
    {
        return $this->menuManager;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choices' => $this->getMenuManager()->getAdminChoices(),
            ]
        );
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
