<?php
namespace Octava\Bundle\AdminMenuBundle\Dict;

use Octava\Bundle\AdminMenuBundle\Entity\AdminMenu;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class Types
 * @package Octava\Bundle\AdminMenuBundle\Dict
 */
class Types
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $translationDomain;

    /**
     * Types constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        return [
            AdminMenu::TYPE_FOLDER => $this->translator->trans(
                'admin.menu_item.type.folder',
                [],
                $this->translationDomain
            ),
            AdminMenu::TYPE_MODULE => $this->translator->trans(
                'admin.menu_item.type.module',
                [],
                $this->translationDomain
            ),
        ];
    }

    /**
     * @param mixed $translationDomain
     * @return self
     */
    public function setTranslationDomain($translationDomain)
    {
        $this->translationDomain = $translationDomain;
        return $this;
    }
}
