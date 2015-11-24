<?php
namespace Octava\Bundle\MuiBundle\Twig;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class TranslationExtension
 * @package Octava\Bundle\MuiBundle\Twig
 */
class TranslationExtension extends \Twig_Extension
{
    const NAME = 'octava_translation';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * TranslationExtension constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    public function getFilters()
    {
        return [
            'serialize' => new \Twig_SimpleFilter('serialize', 'serialize')
        ];
    }

    public function getFunctions()
    {
        return [
            'trans_exists' => new \Twig_SimpleFunction('trans_exists', [$this, 'isTranslationExist'])
        ];
    }

    public function isTranslationExist($id, $parameters = [], $domain = null, $locale = null)
    {
        return $id != $this->translator->trans($id, $parameters, $domain, $locale);
    }
}
