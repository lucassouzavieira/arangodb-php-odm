<?php
declare(strict_types=1);

namespace ArangoDB\Graph;

use ArangoDB\Entity\EntityInterface;
use ArangoDB\DataStructures\ArrayList;

/**
 * Represents an ArangoDB Graph
 *
 * @package ArangoDB\Graph
 * @author Lucas S. Vieira
 */
class Graph
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
     * Graph constructor.
     *
     * @param string $name Graph name
     * @param array $attributes Graph optional attributes
     */
    public function __construct(string $name, array $attributes = [])
    {
        $this->name = $this->key = $name;
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
     * @return ArrayList
     */
    public function getEdgeDefinitions(): ArrayList
    {
        return $this->edgeDefinitions;
    }

    /**
     * @return array
     */
    public function getOrphanCollections(): array
    {
        return $this->orphanCollections;
    }
}
