<?php
declare(strict_types=1);

namespace ArangoDB\Graph\Traversal;

use ArangoDB\AQL\Statement;
use ArangoDB\Document\Vertex;

/**
 * Represents a graph traversal.
 *
 * @package ArangoDB\Graph\Traversal
 * @author Lucas S. Vieira
 */
class Traversal
{
    /**
     * Inbound graph direction.
     *
     * @var string
     */
    public const GRAPH_DIRECTION_INBOUND = 'INBOUND';

    /**
     * Outbound graph direction.
     *
     * @var string
     */
    public const GRAPH_DIRECTION_OUTBOUND = 'OUTBOUND';

    /**
     * Any graph direction.
     *
     * @var string
     */
    public const GRAPH_DIRECTION_ANY = 'ANY';

    /**
     * Vertex to start graph traversal
     *
     * @var Vertex
     */
    protected $vertex;

    /**
     * The graph to traverse
     *
     * @var string
     */
    protected $graph;

    /**
     * Visits only nodes in at least the given depth.
     *
     * @var int
     */
    protected $depth;

    /**
     * The traversal query
     *
     * @var Statement
     */
    protected $statement;

    /**
     * Direction for traversal.
     * Must be either "outbound", "inbound", or "any"
     *
     * @var string
     */
    protected $direction;

    /**
     * Traversal constructor.
     *
     * @param Vertex $vertex Vertex to start graph traversal.
     * @param string $graph The graph to traverse
     * @param string $direction Direction for traversal.
     * @param int $depth Visits only nodes in at least the given depth.
     * @param string $query A custom query. Default is a empty string
     */
    public function __construct(Vertex $vertex, string $graph, string $direction = self::GRAPH_DIRECTION_ANY, int $depth = 0, string $query = "")
    {
        $this->vertex = $vertex;
        $this->direction = $direction;
        $this->depth = $depth === 0 ? 1 : $depth;

        $vertexId = $vertex->getId();

        if (!$query) {
            $query = sprintf("
                FOR v, e, p IN 1..%d %s
                '%s'
                GRAPH '%s'
                LIMIT 100
                RETURN p", $this->depth, $this->direction, $vertexId, $graph);
        }

        $this->statement = new Statement($query);
    }
}
