<?php
namespace Octava\Bundle\MuiBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

/**
 * Class TranslationListType
 * @package Octava\Bundle\MuiBundle\Form\Type
 */
class TranslationListType extends AbstractType
{
    public function getParent()
    {
        return 'text';
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'octava_translation_list_type';
    }
}
