<?php
namespace Octava\Bundle\MenuBundle;

use Octava\Bundle\MenuBundle\Config\MenuConfig;
use Octava\Bundle\MuiBundle\TranslationManager;

/**
 * Class MenuManager
 * @package Octava\Bundle\MenuBundle
 */
class MenuManager
{
    /**
     * @var MenuConfig
     */
    protected $menuConfig;
    /**
     * @var array
     */
    protected $locationsCache;
    /**
     * @var TranslationManager
     */
    protected $translationManager;

    /**
     * MenuManager constructor.
     * @param TranslationManager $translationManager
     * @param MenuConfig $menuConfig
     */
    public function __construct(TranslationManager $translationManager, MenuConfig $menuConfig)
    {
        $this->translationManager = $translationManager;
        $this->menuConfig = $menuConfig;
    }

    /**
     * Получить месторасположения меню,
     * опредённые в конфиге
     * @return array
     */
    public function getLocations()
    {
        if (!is_null($this->locationsCache)) {
            return $this->locationsCache;
        }

        foreach ($this->menuConfig->getLocations() as $location) {
            $this->locationsCache[$location['alias']] = $this->translationManager->trans(
                $location['name'],
                [],
                $location['trans_domain'] ?: null
            );
        }

        return $this->locationsCache;
    }
}
