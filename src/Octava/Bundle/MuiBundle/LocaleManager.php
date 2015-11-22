<?php
namespace Octava\Bundle\MuiBundle;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\MuiBundle\Entity\Locale;

class LocaleManager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var string
     */
    protected $defaultLocale;
    /**
     * @var boolean
     */
    protected $ignoreDefaultLocaleUrl;
    /**
     * @var array
     */
    private $locales;

    public function __construct(EntityManager $manager, $defaultLocale, $ignoreDefaultLocaleUrl = false)
    {
        $this->entityManager = $manager;
        $this->defaultLocale = $defaultLocale;
        $this->ignoreDefaultLocaleUrl = $ignoreDefaultLocaleUrl;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Получить список алиасов активных языков
     * @return array
     */
    public function getAllAliases()
    {
        $result = [];
        foreach ($this->getActiveList() as $locale) {
            $result[] = $locale->getAlias();
        }

        return $result;
    }

    /**
     * Получить список активных языков приложения
     * @return Locale[]
     */
    public function getActiveList()
    {
        if (is_null($this->locales)) {
            $this->locales = $this->getEntityManager()
                ->getRepository('OctavaMuiBundle:Locale')->getActiveList();
        }
        return $this->locales;
    }

    /**
     * @return Locale[]
     */
    public function getAll()
    {
        return $this->getEntityManager()
            ->getRepository('OctavaMuiBundle:Locale')->getAll();
    }

    /**
     * Есть ли активная локаль
     * @param $alias
     * @return bool
     */
    public function hasActiveLocale($alias)
    {
        foreach ($this->getActiveList() as $locale) {
            if ($locale->getAlias() == $alias) {
                return true;
            }
        }
        return false;
    }

    /**
     * Получить флаг, нужно ли удалять язык по-умолчаниб из URL
     * @return boolean
     */
    public function getIgnoreDefaultLocaleFlag()
    {
        return $this->ignoreDefaultLocaleUrl;
    }

    /**
     * Получить алиас языка по-умолчанию из конфига
     * @return string
     */
    public function getDefaultLocaleAlias()
    {
        return $this->defaultLocale;
    }
}
