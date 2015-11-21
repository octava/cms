<?php
namespace Octava\Component\Doctrine;

/**
 * Interface WalkerInterface
 * @package Octava\Component\Doctrine
 */
interface WalkerInterface
{
    /**
     * Callback implementation
     * @param array $rows
     * @param int $offset
     * @param int $limit
     * @return mixed
     */
    public function walk($rows, $offset, $limit);
}
