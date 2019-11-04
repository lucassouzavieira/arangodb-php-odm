<?php
declare(strict_types=1);

namespace ArangoDB\Graph;

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

    public function __construct(array $attributes = [], array $from = [], array $to = [])
    {
    }
}
