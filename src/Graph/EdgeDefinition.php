<?php
declare(strict_types=1);

namespace ArangoDB\Graph;

/**
 * Represents an edge definition for Graphs.
 *
 * @package ArangoDB\Graph
 * @author Lucas S. Vieira
 */
class EdgeDefinition
{
    /**
     * List of vertex collection names. <br>
     * Edges in collection can only be inserted if their _to is in any of the collections here.
     *
     * @var array
     */
    protected array $to = [];

    /**
     * List of vertex collection names. <br>
     * Edges in collection can only be inserted if their _from is in any of the collections here.
     *
     * @var array
     */
    protected array $from = [];

    /**
     * Name of the edge collection.
     *
     * @var string
     */
    protected string $collection;

    /**
     * EdgeDefinition constructor.
     *
     * @param string $collection Edge collection name.
     * @param array $from List of vertex collection names.
     * @param array $to List of vertex collection names.
     */
    public function __construct(string $collection, array $from, array $to)
    {
        $this->to = $to;
        $this->from = $from;
        $this->collection = $collection;
    }

    /**
     * List of vertex collection names. <br>
     * Edges in collection can only be inserted if their _to is in any of the collections here.
     *
     * @return array
     */
    public function to(): array
    {
        return $this->to;
    }

    /**
     * List of vertex collection names. <br>
     * Edges in collection can only be inserted if their _from is in any of the collections here.
     *
     * @return array
     */
    public function from(): array
    {
        return $this->from;
    }

    /**
     * Name of the edge collection.
     *
     * @return string
     */
    public function getCollection(): string
    {
        return $this->collection;
    }

    /**
     * Array representation of edge definition.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'collection' => $this->getCollection(),
            'from' => $this->from(),
            'to' => $this->to()
        ];
    }
}
