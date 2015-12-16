<?php
namespace Octava\Bundle\MuiBundle\Translation;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Gedmo\Translatable\Entity\Translation;
use SimpleThings\EntityAudit\AuditException;
use Sonata\AdminBundle\Model\AuditManager;

class EntityAudit
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var AuditManager
     */
    protected $auditManager;

    /**
     * TranslationManager constructor.
     * @param EntityManager $entityManager
     * @param AuditManager $auditManager
     */
    public function __construct(EntityManager $entityManager, AuditManager $auditManager)
    {
        $this->entityManager = $entityManager;
        $this->auditManager = $auditManager;
    }

    /**
     * @return AuditManager
     */
    public function getAuditManager()
    {
        return $this->auditManager;
    }

    /**
     * @param AuditManager $auditManager
     * @return self
     */
    public function setAuditManager($auditManager)
    {
        $this->auditManager = $auditManager;

        return $this;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     * @return self
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }


    /**
     * @param $object
     * @param $revision
     * @param array $locales
     * @param array $fields
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findRevision($object, $revision, array $locales, array $fields)
    {
        $className = 'Gedmo\\Translatable\\Entity\\Translation';
        $rows = $this->getEntityManager()
            ->getRepository('OctavaMuiBundle:Translation')
            ->findRevision($className, $object, $locales, $fields);

        $result = [];
        foreach ($rows as $row) {
            $locale = $row['locale'];
            if (empty($result)) {
                $result[$locale] = [];
            }
            $field = $row['field'];
            $id = $row['id'];

            /** @var Translation $base */
            $base = $this->findAuditRevision($className, $id, $revision);

            if ($base) {
                $result[$locale][$field] = $base->getContent();
            }
        }
        $result = array_filter($result);

        return $result;
    }

    /**
     * @param $object
     * @param $baseRevision
     * @param $compareRevision
     * @param array $locales
     * @param array $fields
     * @return array
     */
    public function findCompares($object, $baseRevision, $compareRevision, array $locales, array $fields)
    {
        $className = 'Gedmo\\Translatable\\Entity\\Translation';
        $rows = $this->getEntityManager()
            ->getRepository('OctavaMuiBundle:Translation')
            ->findCompares($className, $object, $locales, $fields);

        return $this->prepareCompares($baseRevision, $compareRevision, $rows, $className);
    }

    /**
     * @param $className
     * @param $id
     * @param $rev
     * @return null
     */
    protected function findAuditRevision($className, $id, $rev)
    {
        $result = null;
        try {
            $manager = $this->getAuditManager();
            $reader = $manager->getReader($className);
            $result = $reader->find($className, $id, $rev);
        } catch (AuditException $e) {
        }

        return $result;
    }

    /**
     * @param $baseRevision
     * @param $compareRevision
     * @param $rows
     * @param $className
     * @return array
     */
    protected function prepareCompares($baseRevision, $compareRevision, $rows, $className)
    {
        $result = [];
        foreach ($rows as $row) {
            $locale = $row['locale'];
            if (empty($result)) {
                $result[$locale] = [];
            }
            $field = $row['field'];
            $id = $row['id'];

            /** @var Translation $base */
            $base = $this->findAuditRevision($className, $id, $baseRevision);
            /** @var Translation $compare */
            $compare = $this->findAuditRevision($className, $id, $compareRevision);

            if (is_null($base) && is_null($compare)) {
                continue;
            } elseif (!is_null($base) && !is_null($compare)
                && $base->getContent() == $compare->getContent()
            ) {
                continue;
            }

            $result[$locale][$field]['base'] = $base;
            $result[$locale][$field]['compare'] = $compare;
        }
        $result = array_filter($result);

        return $result;
    }
}
