<?php
namespace Octava\Bundle\StructureBundle\Migration;

use Doctrine\DBAL\Connection;

/**
 * Class StructureMigration
 * @package Octava\Bundle\StructureBundle\Migration
 */
class StructureMigration
{
    const STRUCTURE_TABLE_NAME = 'structure';
    const TRANSLATION_TABLE_NAME = 'ext_translations';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $defaultLocale;

    public function __construct(Connection $connection, $defaultLocale)
    {
        $this->connection = $connection;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    public function generateSql(
        $routeName,
        $type,
        array $state,
        array $title,
        array $description,
        array $alias,
        array $path,
        $parentRouteName = null,
        $template = null
    ) {
        $sql = [];

        $currentDate = (new \DateTime())->format('y-m-d H:i:s');
        $sql[] = sprintf(
            "SELECT @parentId:=id FROM `%s` WHERE `route_name` = '%s' LIMIT 1;",
            self::STRUCTURE_TABLE_NAME,
            $this->connection->quote($parentRouteName)
        );

        $sql[] = sprintf(
            "INSERT INTO `%s`
            (
                `parent_id`,
                `created_at`,
                `updated_at`,
                `title`,
                `description`,
                `type`,
                `alias`,
                `path`,
                `state`,
                `template`,
                `route_name`
              )
            VALUES (
                @parentId,
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s'
            )
            ON DUPLICATE KEY UPDATE
                `parent_id`=VALUES(`parent_id`),
                `updated_at`=VALUES(`updated_at`),
                `title`=VALUES(`title`),
                `description`=VALUES(`description`),
                `type`=VALUES(`type`),
                `alias`=VALUES(`alias`),
                `path`=VALUES(`path`),
                `state`=VALUES(`state`),
                `template`=VALUES(`template`)
            ;",
            self::STRUCTURE_TABLE_NAME,
            $currentDate,
            $currentDate,
            $this->connection->quote($title[$this->defaultLocale]),
            $this->connection->quote($description[$this->defaultLocale]),
            $type,
            $this->connection->quote($alias[$this->defaultLocale]),
            $this->connection->quote($path[$this->defaultLocale]),
            (bool)$state[$this->defaultLocale] ? 1 : 0,
            $this->connection->quote($template),
            $this->connection->quote($routeName)
        );

        $sql[] = sprintf(
            "SELECT @structureId := id FROM `%s` WHERE route_name = '%s';",
            self::STRUCTURE_TABLE_NAME,
            $this->connection->quote($routeName)
        );

        return implode("\n", $sql);
    }
}
