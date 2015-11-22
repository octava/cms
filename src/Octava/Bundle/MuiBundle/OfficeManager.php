<?php
namespace Octava\Bundle\MuiBundle;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\MuiBundle\Entity\Locale;
use Octava\Bundle\MuiBundle\Entity\Office;

/**
 * Class OfficeManager
 * @package Octava\Bundle\MuiBundle
 */
class OfficeManager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var array
     */
    protected $routingOffices = null;
    /**
     * @var Office[]
     */
    protected $officeListByLocale = [];
    /**
     * @var Office[]
     */
    protected $officeListById = [];
    /**
     * @var Locale[]
     */
    protected $availableLanguageEntities = [];
    /**
     * @var Office
     */
    private $currentOffice;

    public function __construct(EntityManager $entityManager, $defaultLocale)
    {
        $this->entityManager = $entityManager;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Returns list of offices
     * @return Office[]
     */
    public function getOffices()
    {
        $ret = $this->entityManager->getRepository('OctavaMuiBundle:Office')->findAll();
        return $ret;
    }

    /**
     * Получить офис по его ID
     * @param $id
     * @return Office
     */
    public function getById($id)
    {
        if (!array_key_exists($id, $this->officeListById)) {
            $this->officeListById[$id] = $this->getEntityManager()
                ->getRepository('OctavaMuiBundle:Office')->find($id);
        }
        return $this->officeListById[$id];
    }

    /**
     * @param $locale
     * @return Office
     */
    public function getByLocale($locale)
    {
        if (!array_key_exists($locale, $this->officeListByLocale)) {
            $this->officeListByLocale[$locale] = $this->getEntityManager()
                ->getRepository('OctavaMuiBundle:Office')
                ->findOneBy(['defaultLanguage' => $locale]);
        }
        return $this->officeListByLocale[$locale];
    }

    /**
     * Returns current office
     * @return Office
     */
    public function getCurrentOffice()
    {
        return $this->currentOffice;
    }

    /**
     * Set current office to static cache
     * @param $currentOffice
     */
    public function setCurrentOffice($currentOffice)
    {
        $this->currentOffice = $currentOffice;
    }

    /**
     * Получить офис по-умолчанию
     * @return Office
     */
    public function getDefault()
    {
        return $this->getByLocale($this->defaultLocale);
    }

    public function getAvailableLanguageEntities(Office $office)
    {
        $aliases = $office->getAvailableLanguages();
        $id = $office->getId();
        if (!array_key_exists($office->getId(), $this->availableLanguageEntities)) {
            $this->availableLanguageEntities[$id] = $this->getEntityManager()
                ->getRepository('OctavaMuiBundle:Locale')
                ->findBy(
                    ['state' => true, 'alias' => $aliases],
                    ['position' => 'ASC']
                );
        }
        return $this->availableLanguageEntities[$id];
    }

    /**
     * @return Office[]
     */
    public function getRoutingOffices()
    {
        if (null === $this->routingOffices) {
            $this->routingOffices = $this->getEntityManager()
                ->getRepository('OctavaMuiBundle:Office')
                ->getRoutingOffices();
        }
        return $this->routingOffices;
    }
}
