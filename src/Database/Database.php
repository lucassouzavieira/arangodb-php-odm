<?php


namespace ArangoDB\Database;

use ArangoDB\Http\Api;
use ArangoDB\Collection\Collection;
use ArangoDB\Connection\Connection;
use ArangoDB\DataStructures\ArrayList;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Exceptions\DatabaseException;

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
     * @var ArrayList
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
     * @throws GuzzleException|DatabaseException
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->database = $this->connection->getDatabaseName();
        $this->collections = $this->retrieveCollections();
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
     * Return all collections of database
     *
     * @return ArrayList[Collection]
     */
    public function getAllCollections(): ArrayList
    {
        return $this->collections;
    }

    /**
     * Check if database has given collection
     *
     * @param string $collection
     * @return bool True if operation was successful, false otherwise
     * @throws GuzzleException|DatabaseException
     */
    public function hasCollection(string $collection): bool
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabaseName(), Api::COLLECTION);
            $uri = sprintf("%s/%s", $uri, $collection);
            $response = $this->connection->get($uri);
            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            // Collection not found.
            if ($exception->getResponse()->getStatusCode() === 404) {
                return false;
            }

            throw $databaseException;
        }
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
     * Returns information about the current database
     *
     * @return array
     * @throws GuzzleException|DatabaseException
     */
    public function getInfo(): array
    {
        return self::current($this->connection);
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

    /**
     * Retrieve a list of collections of database
     *
     * @return ArrayList
     * @throws DatabaseException|GuzzleException
     */
    protected function retrieveCollections(): ArrayList
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabaseName(), Api::COLLECTION);
            $uri = Api::addQuery($uri, ['_' => 1571168105383]);
            $response = $this->connection->get($uri);
            $data = json_decode((string)$response->getBody(), true);

            $nonSystemsCollections = [];
            foreach ($data['result'] as $key => $collection) {
                if (isset($collection['isSystem']) && !$collection['isSystem']) {
                    $nonSystemsCollections[] = $collection;
                }
            }

            return new ArrayList($nonSystemsCollections);
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }
}
