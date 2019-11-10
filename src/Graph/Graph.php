<?php
declare(strict_types=1);

namespace ArangoDB\Graph;

use ArangoDB\Http\Api;
use ArangoDB\Document\Edge;
use ArangoDB\Document\Vertex;
use ArangoDB\Database\Database;
use ArangoDB\DataStructures\ArrayList;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Exceptions\DatabaseException;
use ArangoDB\Validation\Graph\GraphValidator;
use ArangoDB\Exceptions\Exception as ArangoException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Represents an ArangoDB Graph
 *
 * @package ArangoDB\Graph
 * @author Lucas S. Vieira
 */
class Graph implements \JsonSerializable
{
    /**
     * The internal id of this graph
     *
     * @var string
     */
    protected $id;

    /**
     * The name of graph
     *
     * @var string
     */
    protected $key;

    /**
     * If this graph is a new one or a representation of existing document
     *
     * @var bool
     */
    protected $isNew;

    /**
     * Graph name
     *
     * @var string
     */
    protected $name;

    /**
     * Flag if the graph is a smart graph
     *
     * @var bool
     */
    protected $isSmart;

    /**
     * The revision of graph.
     * Can be used to make sure to not override concurrent modifications to this graph
     *
     * @var string
     */
    protected $revision;

    /**
     * Number of shards created for every new collection in the graph.
     *
     * @var int
     */
    protected $numberOfShards;

    /**
     * The replication factor used for every new collection in the graph.
     *
     * @var int
     */
    protected $replicationFactor;

    /**
     * The minimal replication factor used for every new collection in the graph.
     * If one shard has less than minReplicationFactor copies,
     * we cannot write to this shard, but to all others.
     *
     * @var int
     */
    protected $minReplicationFactor;

    /**
     * An array of definitions for the relations of graph.
     *
     * @var ArrayList
     */
    protected $edgeDefinitions;

    /**
     * An array of additional vertex collections.
     * Documents within these collections do not have edges within this graph.
     *
     * @var array
     */
    protected $orphanCollections = [];

    /**
     * Database object of graph
     *
     * @var Database
     */
    protected $database;

    /**
     * Graph constructor.
     *
     * @param string $name Graph name
     * @param array $attributes Graph optional attributes
     * @param Database|null $database Database object
     *
     * @throws InvalidParameterException|MissingParameterException|ArangoException
     */
    public function __construct(string $name, array $attributes = [], Database $database = null)
    {
        $this->name = $this->key = $name;

        if (count($attributes)) {
            $validator = new GraphValidator($attributes);
            $validator->validate();
        }

        $this->isNew = !(isset($attributes['_rev']) && isset($attributes['_id']));

        // Set the given options or fallback to a default value.
        $this->id = isset($attributes['_id']) ? $attributes['_id'] : '';
        $this->revision = isset($attributes['_rev']) ? $attributes['_rev'] : '';
        $this->numberOfShards = isset($attributes['numberOfShards']) ? $attributes['numberOfShards'] : 1;
        $this->replicationFactor = isset($attributes['replicationFactor']) ? $attributes['replicationFactor'] : 1;
        $this->minReplicationFactor = isset($attributes['minReplicationFactor']) ? $attributes['minReplicationFactor'] : 1;
        $this->isSmart = isset($attributes['isSmart']) ? $attributes['isSmart'] : false;
        $this->orphanCollections = isset($attributes['orphanCollections']) ? $attributes['orphanCollections'] : [];
        $this->edgeDefinitions = new ArrayList();

        // Edge definitions passed as array.
        if (isset($attributes['edgeDefinitions']) && is_array($attributes['edgeDefinitions'])) {
            foreach ($attributes['edgeDefinitions'] as $edgeDefinition) {
                $this->edgeDefinitions->push(new EdgeDefinition($edgeDefinition['collection'], $edgeDefinition['from'], $edgeDefinition['to']));
            }
        }

        // Edge definitions passed as ArrayList.
        if (isset($attributes['edgeDefinitions']) && $attributes['edgeDefinitions'] instanceof ArrayList) {
            $this->edgeDefinitions->append($attributes['edgeDefinitions']);
        }

        $this->database = $database;
    }

    /**
     * Returns a string representation of graph object
     *
     * @return string
     */
    public function __toString()
    {
        return print_r($this->toArray(), true);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return bool
     * @see EntityInterface::isNew()
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isSmart(): bool
    {
        return $this->isSmart;
    }

    /**
     * @return string
     */
    public function getRevision(): string
    {
        return $this->revision;
    }

    /**
     * @return int
     */
    public function getNumberOfShards(): int
    {
        return $this->numberOfShards;
    }

    /**
     * @return int
     */
    public function getReplicationFactor(): int
    {
        return $this->replicationFactor;
    }

    /**
     * @return int
     */
    public function getMinReplicationFactor(): int
    {
        return $this->minReplicationFactor;
    }

    /**
     * @return array
     */
    public function getOrphanCollections(): array
    {
        return $this->orphanCollections;
    }

    /**
     * Save a entity on server, if possible
     *
     * @return bool true if operation was successful, false otherwise
     * @throws DatabaseException|GuzzleException|ArangoException
     */
    public function save(): bool
    {
        try {
            if (!$this->database) {
                throw new DatabaseException("Database not defined");
            }

            if ($this->edgeDefinitions->count() === 0) {
                throw new ArangoException("Edges definitions are missing");
            }

            if ($this->isNew()) {
                $connection = $this->database->getConnection();
                $uri = Api::buildSystemUri($connection->getBaseUri(), Api::GRAPH);
                $response = $connection->post($uri, $this->getCreateParameters());
                $data = json_decode((string)$response->getBody(), true);
                $data = $data['graph'];

                // Set descriptors attributes
                $this->id = $data['_id'];
                $this->revision = $data['_rev'];
                $this->isNew = false;

                return true;
            }

            // Cannot create already existing graphs.
            return false;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Removes a graph on server, if possible
     *
     * @param bool $dropCollections If set true, drop collections of this graph as well.
     * Collections will only be dropped if they are not used in other graphs.
     * @return bool true if operation was successful, false otherwise
     *
     * @throws DatabaseException|GuzzleException|ArangoException
     */
    public function delete($dropCollections = false): bool
    {
        try {
            if (!$this->database) {
                throw new DatabaseException("Database not defined");
            }

            // Cannot delete a non-existing graph.
            if ($this->isNew()) {
                return false;
            }

            $connection = $this->database->getConnection();
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::GRAPH);
            $uri = Api::addUriParam($uri, $this->getName());
            $uri = $dropCollections ? Api::addQuery($uri, ['dropCollections' => $dropCollections]) : $uri;
            $response = $connection->delete($uri);
            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Returns a array representation of graph
     *
     * @return array
     */
    public function toArray(): array
    {
        $edges = [];
        foreach ($this->getEdgeDefinitions()->toArray() as $edge) {
            $edges[] = $edge->toArray();
        }

        return [
            '_id' => $this->getId(),
            '_key' => $this->getKey(),
            '_rev' => $this->getRevision(),
            'name' => $this->getName(),
            'isSmart' => $this->isSmart(),
            'edgeDefinitions' => $edges,
            'orphanCollections' => $this->getOrphanCollections(),
            'options' => [
                'numberOfShards' => $this->getNumberOfShards(),
                'replicationFactor' => $this->getReplicationFactor(),
                'minReplicationFactor' => $this->getMinReplicationFactor(),
            ]
        ];
    }

    /**
     * Return all edge definitions of graph
     *
     * @return ArrayList
     */
    public function getEdgeDefinitions(): ArrayList
    {
        return $this->edgeDefinitions;
    }

    /**
     * Adds an edge definition to graph
     *
     * @param string $collection Edge collection name
     * @param array $from List of vertex collection names.
     * @param array $to List of vertex collection names.
     *
     * @return bool
     * @throws DatabaseException|GuzzleException
     */
    public function addEdgeDefinition(string $collection, array $from, array $to): bool
    {
        try {
            $edgeDefinition = new EdgeDefinition($collection, $from, $to);

            // If is a new graph, just add the edge definition to object.
            if ($this->isNew()) {
                $this->edgeDefinitions->push($edgeDefinition);
                return true;
            }

            if (!$this->database) {
                throw new DatabaseException("Database not defined");
            }

            $connection = $this->database->getConnection();
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::GRAPH);
            $uri = sprintf("%s/%s", Api::addUriParam($uri, $this->getName()), 'edge');
            $response = $connection->post($uri, $edgeDefinition->toArray());
            $this->edgeDefinitions->push($edgeDefinition);
            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Remove one edge definition from the graph.
     * This will only remove the edge collection,
     * the vertex collections remain untouched and can still be used in your queries.
     *
     * @param string $collection Edge collection name
     *
     * @param bool $dropCollection Drop the collection as well. Collection will only be dropped if it is not used in other graphs.
     * @param bool $waitForSync Define if the request should wait until synced to disk.
     *
     * @return bool
     * @throws DatabaseException
     * @throws GuzzleException
     */
    public function dropEdgeDefinition(string $collection, bool $dropCollection = false, bool $waitForSync = true): bool
    {
        try {
            // If is a new graph, just return 'false'
            // because we don't have any edge collection defined on server.
            if ($this->isNew()) {
                return false;
            }

            if (!$this->database) {
                throw new DatabaseException("Database not defined");
            }

            // If the database hasn't a collection with the given name, return 'false'
            if (!$this->database->hasCollection($collection)) {
                return false;
            }

            // Delete edge definition of graph
            $connection = $this->database->getConnection();
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::GRAPH);
            $uri = sprintf("%s/%s/%s", Api::addUriParam($uri, $this->getName()), 'edge', $collection);
            $uri = Api::addQuery($uri, ['waitForSync' => $waitForSync, 'dropCollection' => $dropCollection]);
            $response = $connection->delete($uri);

            $toRemove = 0;
            foreach ($this->edgeDefinitions as $key => $edgeDefinition) {
                $toRemove = $edgeDefinition->getCollection() === $collection ? $key : $toRemove;
            }

            $this->edgeDefinitions->remove($toRemove);

            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Gets a vertex from graph
     *
     * @return Vertex
     */
    public function getVertex(): Vertex
    {
    }

    /**
     * Adds a vertex to graph
     *
     * @param Vertex $vertex
     * @return bool
     */
    public function addVertex(Vertex $vertex): bool
    {
    }

    /**
     * Drops a vertex from graph
     *
     * @return bool
     */
    public function dropVertex(): bool
    {
    }

    /**
     * Returns an edge document
     *
     * @return Edge
     */
    public function getEdge(): Edge
    {
    }

    /**
     * Adds an edge to graph
     *
     * @param Edge $edge
     * @return bool
     */
    public function addEdge(Edge $edge): bool
    {
    }

    /**
     * Drops an edge from graph
     *
     * @return bool
     */
    public function dropEdge(): bool
    {
    }

    /**
     * Returns a graph traversal
     *
     * @param Vertex $vertex
     * @param int $depth
     * @param string $type
     *
     * @return Traversal
     */
    public function traversal(Vertex $vertex, int $depth = 0, $type = 'outbound'): Traversal
    {
    }

    /**
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Return an array with parameters to create the graph
     *
     * @return array
     */
    protected function getCreateParameters(): array
    {
        $data = $this->toArray();
        unset($data['_id'], $data['_key'], $data['_rev']);
        return $data;
    }
}
