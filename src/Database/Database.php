<?php
declare(strict_types=1);

namespace ArangoDB\Database;

use ArangoDB\Http\Api;
use ArangoDB\Collection\Collection;
use ArangoDB\Connection\Connection;
use ArangoDB\DataStructures\ArrayList;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Exceptions\DatabaseException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Represents an ArangoDB database
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
     * Informations about database
     *
     * @var array
     */
    protected $info;

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
        $this->sync();
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
     * @throws GuzzleException|DatabaseException
     */
    public function getAllCollections(): ArrayList
    {
        $this->sync();
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
     * @return Collection|bool Collection object. Return False if collection not exists on database
     * @throws DatabaseException|GuzzleException|InvalidParameterException|MissingParameterException
     */
    public function getCollection(string $collection)
    {
        try {
            if ($this->hasCollection($collection)) {
                $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabaseName(), Api::COLLECTION);
                $response = $this->connection->get(sprintf("%s/%s%s", $uri, $collection, Api::COLLECTION_PROPERTIES));
                $data = json_decode((string)$response->getBody(), true);
                return new Collection($data['name'], $this, $data);
            }

            return false;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Create a new collection on database
     *
     * @param string $collection Collection name
     * @param array $attributes If you want to specify some custom attribute to collection
     *
     * @return Collection A Collection object if operation was successful, throws an exception otherwise
     * @throws DatabaseException|GuzzleException|InvalidParameterException|MissingParameterException
     */
    public function createCollection(string $collection, array $attributes = []): Collection
    {
        $collection = new Collection($collection, $this, $attributes);
        $collection->save();
        return $collection;
    }

    /**
     * Drops a given collection of database
     *
     * @param string $collection
     * @return bool True if operation was successful, false otherwise
     * @throws DatabaseException|GuzzleException
     */
    public function dropCollection(string $collection): bool
    {
        if (!$this->hasCollection($collection)) {
            return false;
        }

        $collection = $this->getCollection($collection);
        return $collection->drop();
    }

    /**
     * Returns information about the current database
     *
     * @return array
     * @throws GuzzleException|DatabaseException
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * Synchronizes the object with database on server
     *
     * @return bool
     * @throws GuzzleException|DatabaseException
     */
    public function sync(): bool
    {
        // Update database collections;
        $this->collections = $this->retrieveCollections();

        // Update database info;
        $this->info = self::current($this->connection);
        return true;
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
            $uri = Api::addQuery($uri, ['excludeSystem' => true]);
            $response = $this->connection->get($uri);
            $data = json_decode((string)$response->getBody(), true);

            $collectionList = new ArrayList();
            foreach ($data['result'] as $key => $collection) {
                $collectionList->push(new Collection($collection['name'], $this, $collection));
            }

            return $collectionList;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }
}
