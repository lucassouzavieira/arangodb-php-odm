<?php
declare(strict_types=1);

namespace ArangoDB\Connection;

/**
 * Class ManagesConnection
 *
 * @package ArangoDB\Entity
 * @author Lucas S. Vieira
 */
abstract class ManagesConnection implements ManagesConnectionInterface
{
    /**
     * Connection to access the server
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Sets a connection for class
     *
     * @param Connection $connection
     * @see EntityInterface::setConnection()
     */
    public function setConnection(Connection $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * Return the connection object
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }
}
