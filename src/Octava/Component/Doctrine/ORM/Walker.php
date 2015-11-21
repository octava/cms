<?php
namespace Octava\Component\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Octava\Component\Doctrine\Exception\ORMWalkerException;
use Octava\Component\Doctrine\WalkerInterface;

class Walker
{
    public function run(QueryBuilder $queryBuilder, $callback, $limit = null)
    {
        if (!is_callable($callback)) {
            throw new ORMWalkerException('$callback is not callable');
        }
        if (!is_null($limit)) {
            $queryBuilder->setMaxResults($limit);
        } else {
            $limit = $queryBuilder->getMaxResults();
        }
        if (is_null($limit)) {
            $limit = PHP_INT_MAX;
        }
        $offset = 0;
        do {
            $rows = $queryBuilder->setFirstResult($offset)->getQuery()->getResult();
            call_user_func($callback, $rows, $offset, $limit);
            $offset += $limit;
        } while (count($rows) >= $limit);
    }

    public function runObject(QueryBuilder $queryBuilder, WalkerInterface $callback, $limit = null)
    {
        $this->run($queryBuilder, [$callback, 'walk'], $limit);
    }
}
