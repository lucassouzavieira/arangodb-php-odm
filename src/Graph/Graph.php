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
     * @param string $name Graph name.
     * @param array $attributes Graph optional attributes.
     * @param Database|null $database Database object.
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
     * Returns a string representation of graph object.
     *
     * @return string
     */
    public function __toString()
    {
        return print_r($this->toArray(), true);
    }

    /**
     * Returns the graph ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns the graph key
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Returns true if is a new graph.
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * Gets the graph name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * If this graph is a smartGraph
     *
     * @return bool
     */
    public function isSmart(): bool
    {
        return $this->isSmart;
    }

    /**
     * Gets the graph revision.
     *
     * @return string
     */
    public function getRevision(): string
    {
        return $this->revision;
    }

    /**
     * Returns the number of shards that is used for every collection within this graph.
     *
     * @return int
     */
    public function getNumberOfShards(): int
    {
        return $this->numberOfShards;
    }

    /**
     * Returns the replication factor used when initially creating collections for this graph.
     *
     * @return int
     */
    public function getReplicationFactor(): int
    {
        return $this->replicationFactor;
    }

    /**
     * Returns the minimal replication factor used for every new collection in the graph.
     *
     * @return int
     */
    public function getMinReplicationFactor(): int
    {
        return $this->minReplicationFactor;
    }

    /**
     * Returns the orphan collections in the graph.
     *
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
     *
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
     * @param bool $dropCollections If set true, drop collections of this graph as well. <br>
     * Collections will only be dropped if they are not used in other graphs.
     *
     * @return bool True if operation was successful, false otherwise.
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
     * @param string $collection Edge collection name.
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
     * @param string $collection Edge collection name.
     * @param bool $dropCollection Drop the collection as well. Collection will only be dropped if it is not used in other graphs.
     * @param bool $waitForSync Define if the request should wait until synced to disk.
     *
     * @return bool
     * @throws DatabaseException|GuzzleException
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
     * Lists all vertex collections within this graph.
     *
     * @return ArrayList
     *
     * @throws DatabaseException|GuzzleException|ArangoException
     */
    public function getVertexCollections(): ArrayList
    {
        try {
            // Empty vertex collections for new graphs
            if ($this->isNew()) {
                return new ArrayList();
            }

            if (!$this->database) {
                throw new DatabaseException("Database not defined");
            }

            $connection = $this->database->getConnection();
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::GRAPH);
            $uri = sprintf("%s/%s", Api::addUriParam($uri, $this->getName()), 'vertex');
            $response = $connection->get($uri);
            $data = json_decode((string)$response->getBody(), true);
            return new ArrayList($data['collections']);
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Adds a vertex collection to the set of orphan collections of the graph.
     * If the collection does not exist, it will be created.
     *
     * @param string $collection The name of the vertex collection.
     *
     * @return bool
     *
     * @throws DatabaseException|GuzzleException|ArangoException
     */
    public function addVertexCollection(string $collection): bool
    {
        try {
            // For new collections, just add the collection to 'orphanCollections'
            if ($this->isNew()) {
                $this->orphanCollections[] = $collection;
                return true;
            }

            if (!$this->database) {
                throw new DatabaseException("Database not defined");
            }

            // Adds the vertex collection on server.
            $connection = $this->database->getConnection();
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::GRAPH);
            $uri = sprintf("%s/%s", Api::addUriParam($uri, $this->getName()), 'vertex');
            $response = $connection->post($uri, ['collection' => $collection]);
            $this->orphanCollections[] = $collection;
            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Removes a vertex collection from the graph and optionally deletes the collection, if it is not used in any other graph.<br>
     * It can only remove vertex collections that are no longer part of edge definitions,<br>
     * if they are used in edge definitions you are required to modify those first.
     *
     * @param string $collection The name of the vertex collection.
     * @param bool $dropCollection Drop the collection as well. Collection will only be dropped if it is not used in other graphs.
     *
     * @return bool
     *
     * @throws DatabaseException|GuzzleException|ArangoException
     */
    public function dropVertexCollection(string $collection, bool $dropCollection = false): bool
    {
        try {
            // For new collections, we don't have any collections on server.
            if ($this->isNew()) {
                return false;
            }

            if (!$this->database) {
                throw new DatabaseException("Database not defined");
            }

            // Drops the vertex collection on server.
            $connection = $this->database->getConnection();
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::GRAPH);
            $uri = sprintf("%s/%s/%s", Api::addUriParam($uri, $this->getName()), 'vertex', $collection);
            $uri = Api::addQuery($uri, ['dropCollection' => $dropCollection]);
            $response = $connection->delete($uri);
            $data = json_decode((string)$response->getBody(), true);
            $this->orphanCollections = $data['graph']['orphanCollections'];
            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Gets a vertex from the given collection.
     *
     * @param string $collection The name of the vertex collection the vertex belongs to.
     * @param string $vertex The _key attribute of the vertex.
     *
     * @return Vertex|false A Vertex object if vertex exists. False if no graph with this name could be found<br>
     * or this collection is not part of the graph or the vertex does not exist.
     *
     * @throws DatabaseException|GuzzleException|InvalidParameterException|MissingParameterException
     */
    public function getVertex(string $collection, string $vertex)
    {
        try {
            // New graphs doesn't have vertex on server.
            if ($this->isNew()) {
                return false;
            }

            if (!$this->database) {
                throw new DatabaseException("Database not defined");
            }

            $connection = $this->database->getConnection();
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::GRAPH);
            $uri = sprintf("%s/%s/%s/%s", Api::addUriParam($uri, $this->getName()), 'vertex', $collection, $vertex);
            $response = $connection->get($uri);
            $data = json_decode((string)$response->getBody(), true);
            return new Vertex($data['vertex'], $this->database->getCollection($collection));
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);

            if ($exception->getResponse()->getStatusCode() === 404) {
                return false;
            }

            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Adds a vertex to graph.
     *
     * @param string $collection The name of the vertex collection the vertex should be inserted into.
     * @param array $attributes The object attributes to be stored.
     * @param bool $waitForSync Define if the request should wait until synced to disk. Default is true.
     * @param bool $returnNew Define if the response should contain the complete new version of the document. Default is true.
     *
     * @return bool True if the vertex could be added. False if no graph with this name could be found<br>
     * or if a graph is found but the collection is not part of the graph.
     *
     * @throws DatabaseException|GuzzleException|ArangoException
     */
    public function addVertex(string $collection, array $attributes = [], bool $waitForSync = true, bool $returnNew = true): bool
    {
        try {
            // New graphs can't add vertices on server.
            if ($this->isNew()) {
                return false;
            }

            if (!$this->database) {
                throw new DatabaseException("Database not defined");
            }

            $connection = $this->database->getConnection();
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::GRAPH);
            $uri = sprintf("%s/%s/%s", Api::addUriParam($uri, $this->getName()), 'vertex', $collection);
            $uri = Api::addQuery($uri, ['waitForSync' => $waitForSync, 'returnNew' => $returnNew]);
            $response = $connection->post($uri, $attributes);
            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);

            if ($exception->getResponse()->getStatusCode() === 404) {
                return false;
            }

            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Drops a vertex from graph.
     *
     * @param string $collection The name of the vertex collection the vertex belongs to.
     * @param string $vertex The _key attribute of the vertex.
     * @param bool $waitForSync Define if the request should wait until synced to disk. Default is true.
     * @param bool $returnOld Define if the response should contain the complete new version of the document. Default is false.
     *
     * @return bool True if the vertex could be removed. False if no graph with this name could be found<br>
     * or if a graph is found but the collection is not part of the graph.
     *
     * @throws DatabaseException|GuzzleException|ArangoException
     */
    public function dropVertex(string $collection, string $vertex, bool $waitForSync = true, bool $returnOld = false): bool
    {
        try {
            // New graphs doesn't have vertex on server.
            if ($this->isNew()) {
                return false;
            }

            if (!$this->database) {
                throw new DatabaseException("Database not defined");
            }

            $connection = $this->database->getConnection();
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::GRAPH);
            $uri = sprintf("%s/%s/%s/%s", Api::addUriParam($uri, $this->getName()), 'vertex', $collection, $vertex);
            $uri = Api::addQuery($uri, ['waitForSync' => $waitForSync, 'returnOld' => $returnOld]);

            $response = $connection->delete($uri);
            $data = json_decode((string)$response->getBody(), true);
            return $data['removed'];
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);

            if ($exception->getResponse()->getStatusCode() === 404) {
                return false;
            }

            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Returns an edge document.
     *
     * @param string $collection The name of the edge collection the edge belongs to.
     * @param string $edge The _key attribute of the edge.
     *
     * @return Edge|false A Edge object if edge exists. False if no graph with this name could be found<br>
     * or this collection is not part of the graph or the edge does not exist.
     *
     * @throws DatabaseException|GuzzleException|ArangoException|MissingParameterException|InvalidParameterException
     */
    public function getEdge(string $collection, string $edge)
    {
        try {
            // New graphs doesn't have edges on server.
            if ($this->isNew()) {
                return false;
            }

            if (!$this->database) {
                throw new DatabaseException("Database not defined");
            }

            $connection = $this->database->getConnection();
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::GRAPH);
            $uri = sprintf("%s/%s/%s/%s", Api::addUriParam($uri, $this->getName()), 'edge', $collection, $edge);
            $response = $connection->get($uri);
            $data = json_decode((string)$response->getBody(), true);
            return new Edge($data['edge'], $this->database->getCollection($collection));
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);

            if ($exception->getResponse()->getStatusCode() === 404) {
                return false;
            }

            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Creates a new edge in the collection.<br>
     * Within the attributes the edge has to contain a _from and _to value referencing to valid vertices in the graph.
     * Furthermore the edge has to be valid in the definition of the used
     *
     * @param string $collection The name of the edge collection the edge belongs to.
     * @param array $attributes The object attributes to be stored. Must contains '_to' and '_from' keys.
     * @param bool $waitForSync Define if the request should wait until synced to disk. Default is true.
     * @param bool $returnNew Define if the response should contain the complete new version of the document. Default is false
     *
     * @return Edge|false A Edge object if edge exists. False if no graph with this name could be found
     * or this collection is not part of the graph or one of the vertices ('_to' or '_from') does not exist.
     *
     * @throws DatabaseException|GuzzleException|ArangoException
     */
    public function addEdge(string $collection, array $attributes = [], bool $waitForSync = true, bool $returnNew = false): bool
    {
        try {
            // New graphs can't add edges on server.
            if ($this->isNew()) {
                return false;
            }

            if (!$this->database) {
                throw new DatabaseException("Database not defined");
            }

            $connection = $this->database->getConnection();
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::GRAPH);
            $uri = sprintf("%s/%s/%s", Api::addUriParam($uri, $this->getName()), 'edge', $collection);
            $uri = Api::addQuery($uri, ['waitForSync' => $waitForSync, 'returnNew' => $returnNew]);
            $response = $connection->post($uri, $attributes);
            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);

            if ($exception->getResponse()->getStatusCode() === 404) {
                return false;
            }

            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Drops an edge from graph.
     *
     * @param string $collection The name of the edge collection the edge belongs to.
     * @param string $edge The _key attribute of the edge.
     * @param bool $waitForSync Define if the request should wait until synced to disk. Default is true.
     * @param bool $returnOld Define if the response should contain the complete new version of the document. Default is false.
     *
     * @return bool True if the edge could be removed. False if no graph with this name could be found<br>
     * or if a graph is found but the collection is not part of the graph.
     *
     * @throws DatabaseException|GuzzleException|ArangoException
     */
    public function dropEdge(string $collection, string $edge, bool $waitForSync = true, bool $returnOld = false): bool
    {
        try {
            // New graphs doesn't have edges on server.
            if ($this->isNew()) {
                return false;
            }

            if (!$this->database) {
                throw new DatabaseException("Database not defined");
            }

            $connection = $this->database->getConnection();
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::GRAPH);
            $uri = sprintf("%s/%s/%s/%s", Api::addUriParam($uri, $this->getName()), 'edge', $collection, $edge);
            $uri = Api::addQuery($uri, ['waitForSync' => $waitForSync, 'returnOld' => $returnOld]);

            $response = $connection->delete($uri);
            $data = json_decode((string)$response->getBody(), true);
            return $data['removed'];
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);

            if ($exception->getResponse()->getStatusCode() === 404) {
                return false;
            }

            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Returns a graph traversal
     *
     * @param Vertex $vertex The start vertex.
     * @param int $depth Visits only nodes in at least the given depth.
     * @param string $direction Direction for traversal. Must be either "outbound", "inbound", or "any".
     *
     * @return Traversal
     */
    public function traversal(Vertex $vertex, $direction = Traversal::GRAPH_DIRECTION_ANY, int $depth = 0): Traversal
    {
        return new Traversal($vertex, $direction, $depth);
    }

    /**
     * Return a JSON representation of graph attributes.
     *
     * @return array|mixed
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
    private function getCreateParameters(): array
    {
        $data = $this->toArray();
        unset($data['_id'], $data['_key'], $data['_rev']);
        return $data;
    }
}
