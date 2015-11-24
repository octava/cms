<?php
namespace Octava\Bundle\MuiBundle\Twig;

use Octava\Bundle\MuiBundle\Entity\Office;
use Octava\Bundle\MuiBundle\OfficeManager;

/**
 * Class OfficeExtension
 * @package Octava\Bundle\MuiBundle\Twig
 */
class OfficeExtension extends \Twig_Extension
{
    const NAME = 'octava_offices';

    /**
     * @var OfficeManager
     */
    protected $officeManager;

    public function __construct(OfficeManager $officeManager)
    {
        $this->officeManager = $officeManager;
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

    public function getFunctions()
    {
        return [
            'offices' => new \Twig_SimpleFunction('offices', [$this, 'getOffices']),
            'office_locales' => new \Twig_SimpleFunction('office_locales', [$this, 'getOfficeLocales'])
        ];
    }

    /**
     * @return Office[]
     */
    public function getOffices()
    {
        return $this->officeManager->getOffices();
    }

    public function getOfficeLocales()
    {
        $office = $this->officeManager->getCurrentOffice();
        $locales = [];
        if ($office) {
            $locales = $this->officeManager->getAvailableLanguageEntities($office);
        }
        return $locales;
    }
}
