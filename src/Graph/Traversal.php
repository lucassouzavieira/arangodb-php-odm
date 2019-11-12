<?php
declare(strict_types=1);

namespace ArangoDB\Graph;

use ArangoDB\Document\Vertex;

/**
 * Represents a graph traversal.
 *
 * @package ArangoDB\Graph
 * @author Lucas S. Vieira
 */
class Traversal
{
    /**
     * Inbound graph direction.
     *
     * @var string
     */
    public const GRAPH_DIRECTION_INBOUND = 'inbound';

    /**
     * Outbound graph direction.
     *
     * @var string
     */
    public const GRAPH_DIRECTION_OUTBOUND = 'outbound';

    /**
     * Any graph direction.
     *
     * @var string
     */
    public const GRAPH_DIRECTION_ANY = 'any';

    /**
     * Vertex to start graph traversal
     *
     * @var Vertex
     */
    protected $vertex;

    /**
     * Visits only nodes in at least the given depth.
     *
     * @var int
     */
    protected $depth;

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
     * @param string $direction Direction for traversal.
     * @param int $depth Visits only nodes in at least the given depth.
     */
    public function __construct(Vertex $vertex, string $direction = self::GRAPH_DIRECTION_ANY, int $depth = 0)
    {
        $this->vertex = $vertex;
        $this->direction = $direction;
        $this->depth = $depth;
    }
}
