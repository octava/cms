<?php
namespace Octava\Bundle\MuiBundle\Config;

use Symfony\Component\Translation\TranslatorInterface;

class AdminLocalesConfig extends \Twig_Extension
{
    /**
     * @var array
     */
    protected $locales = [];

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(array $locales = ['en', 'ru'])
    {
        $this->locales = $locales;
    }

    /**
     * @param TranslatorInterface $translator
     * @return $this
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * @return array
     */
    public function getLocales()
    {
        return $this->locales;
    }

    public function getTemplateLocales()
    {
        $ret = [];
        foreach ($this->locales as $locale) {
            $ret[] = [
                'locale' => $locale,
                'name' => $this->translator->trans('admin.languages.name_'.$locale, [], 'OctavaAdminBundle'),
                'image' => $this->translator->trans('admin.languages.image_'.$locale, [], 'OctavaAdminBundle'),
            ];
        }

        return $ret;
    }

    public function getFunctions()
    {
        return [
            'admin_locales' => new \Twig_SimpleFunction('admin_locales', [$this, 'getTemplateLocales']),
        ];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'octava_admin_locale_config';
    }
}
