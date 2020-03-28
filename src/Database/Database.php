<?php
declare(strict_types=1);

namespace ArangoDB\Database;

use ArangoDB\Http\Api;
use ArangoDB\Graph\Graph;
use ArangoDB\Exceptions\Exception;
use ArangoDB\Collection\Collection;
use ArangoDB\Connection\Connection;
use ArangoDB\DataStructures\ArrayList;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Exceptions\Database\DatabaseException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Represents an ArangoDB database
 *
 * @package ArangoDB\Database
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
     * Information about database
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
     * @param Connection $connection Connection object to be used
     *
     * @throws GuzzleException|DatabaseException|InvalidParameterException|MissingParameterException
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
     * @return ArrayList A list with all collections in database.
     *
     * @throws GuzzleException|DatabaseException|InvalidParameterException|MissingParameterException
     */
    public function getAllCollections(): ArrayList
    {
        $this->sync();
        return $this->collections;
    }

    /**
     * Check if database has given collection
     *
     * @param string $collection Collection name
     *
     * @return bool True if operation was successful, false otherwise
     *
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
     * @param string $collection Collection name
     *
     * @return Collection|bool Collection object. Return False if collection not exists on database
     *
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
     * Lists all graphs stored in this database.
     *
     * @return ArrayList A list with all graphs in database.
     *
     * @throws DatabaseException|GuzzleException|InvalidParameterException|MissingParameterException|Exception
     */
    public function getAllGraphs(): ArrayList
    {
        try {
            $uri = Api::buildSystemUri($this->connection->getBaseUri(), Api::GRAPH);
            $response = $this->connection->get($uri);
            $data = json_decode((string)$response->getBody(), true);
            $graphs = new ArrayList();
            foreach ($data['graphs'] as $graphData) {
                $graphs->push(new Graph($graphData['_key'], $graphData, $this));
            }

            return $graphs;
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
     *
     * @throws DatabaseException|GuzzleException|InvalidParameterException|MissingParameterException
     * @see https://www.arangodb.com/docs/stable/http/collection-creating.html#create-collection
     */
    public function createCollection(string $collection, array $attributes = []): Collection
    {
        $collection = new Collection($collection, $this, $attributes);
        $collection->save();
        return $collection;
    }

    /**
     * Check if database has a given graph
     *
     * @param string $graph Graph name.
     *
     * @return Graph|false Returns a Graph object if graph exists. Return False if graph not exists on database.
     *
     * @throws DatabaseException|Exception|GuzzleException|InvalidParameterException|MissingParameterException
     */
    public function getGraph(string $graph)
    {
        try {
            $uri = Api::buildSystemUri($this->connection->getBaseUri(), Api::GRAPH);
            $uri = Api::addUriParam($uri, $graph);
            $response = $this->connection->get($uri);
            $data = json_decode((string)$response->getBody(), true);
            $graph = new Graph($graph, $data['graph'], $this);
            return $graph;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);

            // Graph not found.
            if ($exception->getResponse()->getStatusCode() === 404) {
                return false;
            }

            throw $databaseException;
        }
    }

    /**
     * Drops a given collection of database
     *
     * @param string $collection Collection name
     *
     * @return bool True if operation was successful, false otherwise
     *
     * @throws DatabaseException|GuzzleException|InvalidParameterException|MissingParameterException
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
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * Synchronizes the object with database on server
     *
     * @throws GuzzleException|DatabaseException|InvalidParameterException|MissingParameterException
     */
    private function sync()
    {
        // Update database collections;
        $this->collections = $this->retrieveCollections();

        // Update database info;
        $this->info = self::current($this->connection);
    }

    /**
     * Retrieve a list of collections of database
     *
     * @return ArrayList A list with all collections in database.
     *
     * @throws DatabaseException|GuzzleException|InvalidParameterException|MissingParameterException
     */
    private function retrieveCollections(): ArrayList
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
