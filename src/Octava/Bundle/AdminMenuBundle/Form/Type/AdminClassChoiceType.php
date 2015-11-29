<?php
namespace Octava\Bundle\AdminMenuBundle\Form\Type;

use Octava\Bundle\AdminMenuBundle\AdminMenuManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminClassChoiceType extends AbstractType
{
    const TYPE_NAME = 'octava_admin_menu_admin_class_choice';

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
        $list = $this->getMenuManager()->getAdminChoices();
        $keys = array_keys($list);
        $resolver->setDefaults(
            [
                'admin_class_list' => $list,
                'choices' => array_combine($keys, $keys),
            ]
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['admin_class_list'] = $options['admin_class_list'];
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::TYPE_NAME;
    }

    public function getParent()
    {
        return 'choice';
    }
}
