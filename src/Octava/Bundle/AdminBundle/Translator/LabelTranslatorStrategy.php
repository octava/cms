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
        if ($label == '_action') {
            return $this->translator->trans('admin._action', [], 'OctavaAdminBundle');
        }
        if ($label == 'id') {
            return $this->translator->trans('admin.id', [], 'OctavaAdminBundle');
        }

        $label = str_replace('.', '_', $label);

        return sprintf('admin.%s', strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $label)));
    }
}
