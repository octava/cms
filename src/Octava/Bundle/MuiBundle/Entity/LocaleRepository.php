<?php

namespace Octava\Bundle\MuiBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * LocaleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LocaleRepository extends EntityRepository
{
    /**
     * @param array $exceptAliases
     * @return array
     */
    public function findForChoices(array $exceptAliases = [])
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->select('l.id', 'l.alias')
            ->orderBy('l.position');
        if ($exceptAliases) {
            $queryBuilder->where($queryBuilder->expr()->notIn('l.alias', $exceptAliases));
        }
        $data = $queryBuilder->getQuery()->getResult();

        $result = [];
        foreach ($data as $item) {
            $result[$item['alias']] = $item['alias'];
        }

        return $result;
    }

    /**
     * @return Locale[]
     */
    public function getActiveList()
    {
        return $this->createQueryBuilder('l')
            ->where('l.state = 1')
            ->orderBy('l.position')
            ->getQuery()->getResult();
    }

    /**
     * @return Locale[]
     */
    public function getAll()
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.position')
            ->getQuery()->getResult();
    }
}