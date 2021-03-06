<?php
namespace Octava\Bundle\AdminBundle\Translator;

use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class LabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @param Translator $translator
     * @return $this
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * @param string $label
     * @param string $context
     * @param string $type
     *
     * @return string
     */
    public function getLabel($label, $context = '', $type = '')
    {
        $deleteStr = '_delete';
        if ($context == 'breadcrumb' && substr($label, -strlen($deleteStr)) == $deleteStr) {
            return $this->translator->trans('admin.delete', [], 'OctavaAdminBundle');
        }
        if (in_array($label, ['_action', 'Action'])) {
            return $this->translator->trans('admin._action', [], 'OctavaAdminBundle');
        }
        if (in_array($label, ['Actions'])) {
            return $this->translator->trans('Actions', [], 'OctavaAdminBundle');
        }
        if ($label == 'id') {
            return $this->translator->trans('admin.id', [], 'OctavaAdminBundle');
        }
        if (in_array($label, ['created_at', 'createdAt', 'Created At'])) {
            return $this->translator->trans('admin.created_at', [], 'OctavaAdminBundle');
        }
        if (in_array($label, ['updated_at', 'updatedAt', 'Updated At'])) {
            return $this->translator->trans('admin.updated_at', [], 'OctavaAdminBundle');
        }

        $label = str_replace('.', '_', $label);

        $result = preg_replace('/^translation___(.*)___[a-z]{2}$/i', '$1', $label);
        $result = strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $result));

        $result = 'admin.'.$result;

        return $result;
    }
}
