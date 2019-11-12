<?php
declare(strict_types=1);

namespace ArangoDB\Connection;

/**
 * Basic connection management class
 *
 * @package ArangoDB\Connection
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
     * Sets a connection for class.
     *
     * @param Connection $connection Connection object to use.
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
