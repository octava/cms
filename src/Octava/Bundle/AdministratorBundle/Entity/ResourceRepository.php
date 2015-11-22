<?php

namespace Octava\Bundle\AdministratorBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Octava\Bundle\AdministratorBundle\Entity\Resource as EntityResource;

/**
 * ResourceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ResourceRepository extends EntityRepository
{
    protected $accessList = [];

    public function getActions()
    {
        $queryBuilder = $this->createQueryBuilder('r')
            ->select('DISTINCT r.action')
            ->from('Robo\AdministratorBundle\Entity\Resource', 'r1')
            ->getQuery();
        $rows = $queryBuilder->getResult();
        $ret = [];
        foreach ($rows as $row) {
            $ret[] = $row['action'];
        }
        return $ret;
    }

    public function getList()
    {
        if (!empty($this->accessList)) {
            return $this->accessList;
        }
        /** @var EntityResource[] $a */
        $rows = $this->findAll();
        foreach ($rows as $row) {
            /** @var EntityResource $row */
            $this->accessList[$row->getResource()][$row->getAction()] = $row->getId();
        }
        return $this->accessList;
    }

    public function isVisible($admin, $action, $user)
    {
        /** @var Administrator $user */
        $resource = get_class($admin);
        $ret = $this->findOneBy(['resource' => $resource, 'action' => $action]);
        return !$ret instanceof Resource || !$ret->getHidden();
    }

    public function getModules()
    {
        $queryBuilder = $this->createQueryBuilder('r')
            ->select('r.resource', 'r.label')
            ->distinct()
            ->orderBy('r.sort');
        return $queryBuilder->getQuery()->getArrayResult();
    }
}