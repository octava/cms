<?php
namespace Octava\Bundle\MenuBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuRelatedTextType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['from_field'] = $options['from_field'];
        $view->vars['proxy_field'] = $options['proxy_field'];
        $view->vars['structure_value'] = $options['structure_value'];
        $view->vars['locale'] = $options['locale'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'from_field' => '',
                'proxy_field' => '',
                'structure_value' => '',
                'locale' => '',
            ]
        );
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'octava_menu_related_text';
    }
}
