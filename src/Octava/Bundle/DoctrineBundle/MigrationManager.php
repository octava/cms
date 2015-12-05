<?php
namespace Octava\Bundle\DoctrineBundle;

use Doctrine\DBAL\Connection;

/**
 * Class MigrationManager
 * @package Octava\Bundle\DoctrineBundle
 */
abstract class MigrationManager
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
}
