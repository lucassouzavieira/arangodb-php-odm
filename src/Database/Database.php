<?php


namespace ArangoDB\Database;

use ArangoDB\Collection\Collection;
use ArangoDB\Connection\Connection;
use ArangoDB\Entity\ManagesConnectionInterface;

/**
 * Represents a database on server
 *
 * @package ArangoDB\Auth
 * @author Lucas S. Vieira
 */
class Database extends DatabaseHandler
{
    /**
     * Database name
     *
     * @var string
     */
    protected $database;

    /**
     * Collections of database
     *
     * @var array
     */
    protected $collections;

    /**
     * Connection to use to manage database
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Database constructor.
     *
     * @param Connection $connection Connection to use
     */
    public function __construct(Connection $connection)
    {
        $this->database = $connection->getDatabaseName();
        $this->connection = $connection;
    }

    /**
     * Return the name of database handled
     *
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->database;
    }

    /**
     * Check if database has given collection
     *
     * @param string $collection
     * @return bool True if operation was successful, false otherwise
     */
    public function hasCollection(string $collection): bool
    {
        // TODO: Implement hasCollection method
    }

    /**
     * Return the collection object for a given collection
     *
     * @param string $collection
     * @return Collection Collection object. Throws an exception if collection not exists on database
     */
    public function getCollection(string $collection): Collection
    {
        // TODO: Implement getCollection method
    }

    /**
     * Create a new collection on database
     *
     * @param string $collection
     * @return bool True if operation was successful, false otherwise
     */
    public function createCollection(string $collection): bool
    {
        // TODO: Implement createCollection method
    }

    /**
     * Drops a given collection of database
     *
     * @param string $collection
     * @return bool True if operation was successful, false otherwise
     */
    public function dropCollection(string $collection): bool
    {
        // TODO: Implement dropCollection method
    }

    /**
     * Synchronizes the object with database on server
     *
     * @return bool
     */
    public function sync(): bool
    {
        // TODO: Implement sync method
    }
}