<?php
namespace Octava\Bundle\AdminMenuBundle\Form\Type;

use Octava\Bundle\AdminMenuBundle\AdminMenuManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityType extends AbstractType
{
    const TYPE_NAME = 'octava_admin_menu_entity';

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

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::TYPE_NAME;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'tree_list' => $this->getMenuManager()->getFolderChoices(),
                'excluded_ids' => [],
                'default_data' => null,
            ]
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['tree_list'] = $options['tree_list'];
        $view->vars['excluded_ids'] = $options['excluded_ids'];
        $view->vars['default_data'] = $options['default_data'];
    }

    public function getParent()
    {
        return 'entity';
    }
}
