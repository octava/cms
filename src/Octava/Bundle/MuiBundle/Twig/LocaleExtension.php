<?php
namespace Octava\Bundle\MuiBundle\Twig;

use Octava\Bundle\MuiBundle\Entity\Locale;
use Octava\Bundle\MuiBundle\Entity\Office;
use Octava\Bundle\MuiBundle\LocaleManager;
use Octava\Bundle\MuiBundle\OfficeManager;
use Symfony\Component\HttpFoundation\Request;

class LocaleExtension extends \Twig_Extension
{
    /**
     * @var LocaleManager
     */
    protected $manager;

    /**
     * @var OfficeManager
     */
    protected $officeManager;

    /**
     * @var Office[]
     */
    protected $offices = [];

    public function __construct(LocaleManager $manager, OfficeManager $officeManager)
    {
        $this->manager = $manager;
        $this->officeManager = $officeManager;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'octava_locale';
    }

    public function getFunctions()
    {
        return [
            'locales' => new \Twig_SimpleFunction('locales', [$this, 'getLocales']),
            'locale_path' => new \Twig_SimpleFunction('locale_path', [$this, 'getLocalePath']),
        ];
    }

    public function getFilters()
    {
        return [
            'locale_link' => new \Twig_SimpleFilter('locale_link', [$this, 'filterLocaleLink']),
        ];
    }

    public function getLocales()
    {
        return $this->manager->getActiveList();
    }

    public function getLocalePath(Locale $locale, Request $request)
    {
        $arrPath = explode('/', ltrim($request->getPathInfo(), '/'));
        if (in_array($arrPath[0], $this->manager->getAllAliases())) {
            unset($arrPath[0]);
        }

        $resultUrl = '/' . implode('/', $arrPath);
        if (!$this->manager->getIgnoreDefaultLocaleFlag() ||
            $locale->getAlias() != $this->manager->getDefaultLocaleAlias()
        ) {
            $resultUrl = '/' . $locale->getAlias() . $resultUrl;
        }

        return $resultUrl;
    }

    /**
     * Подставить сегмент языка в ссылку
     * @param string $path
     * @param $locale
     * @return string
     */
    public function filterLocaleLink($path, $locale = null)
    {
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return $path;
        }

        if ($locale) {
            $office = $this->officeManager->getByLocale($locale);
        } else {
            $office = $this->officeManager->getCurrentOffice();
            if (!$office) {
                $office = $this->officeManager->getDefault();
            }
        }

        if (empty($this->offices)) {
            $this->offices = $this->officeManager->getRoutingOffices();
        }
        $languages = array_keys($this->offices);

        $resultUrl = $path;
        if ($office->getIncludeLangInUrl()) {
            $arrPath = explode('/', ltrim($path, '/'));
            if (empty($arrPath[0]) || !in_array($arrPath[0], $languages)) {
                $resultUrl = '/' . $office->getDefaultLanguage() . '/' . ltrim($path, '/');
            }
        }

        return $resultUrl;
    }
}
