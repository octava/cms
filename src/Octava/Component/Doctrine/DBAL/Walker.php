<?php
namespace Octava\Component\Doctrine\DBAL;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Octava\Component\Doctrine\Exception\DBALWalkerException;
use Octava\Component\Doctrine\WalkerInterface;

class Walker
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $callback
     * @param int|null $limit
     * @param int $maxAttempts
     * @throws DBALWalkerException
     * @throws DBALException
     */
    public function run(QueryBuilder $queryBuilder, $callback, $limit = null, $maxAttempts = 10)
    {
        if (!is_callable($callback)) {
            throw new DBALWalkerException('$callback is not callable');
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
            $rows = [];
            $errors = $maxAttempts;
            while (true) {
                try {
                    $rows = $queryBuilder->setFirstResult($offset)->execute()->fetchAll();
                    break;
                } catch (DBALException $e) {
                    if ($errors-- == 0) {
                        throw $e;
                    }
                    usleep(100);
                    $queryBuilder->getConnection()->connect();
                }
            }
            call_user_func($callback, $rows, $offset, $limit);
            $offset += $limit;
        } while (count($rows) >= $limit);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param WalkerInterface $callback
     * @param int $limit
     * @param int $maxAttempts
     * @throws DBALException
     * @throws DBALWalkerException
     * @throws \Exception
     */
    public function runObject(QueryBuilder $queryBuilder, WalkerInterface $callback, $limit = null, $maxAttempts = 10)
    {
        $this->run($queryBuilder, [$callback, 'walk'], $limit, $maxAttempts);
    }
}
