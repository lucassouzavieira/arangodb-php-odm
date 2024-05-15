<?php
declare(strict_types=1);

namespace ArangoDB\Graph\Traversal;

use ArangoDB\Document\Edge;
use ArangoDB\Document\Vertex;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Represents a traversal path.
 *
 * @package ArangoDB\Graph\Traversal
 * @author Lucas S. Vieira
 */
class Path
{
    /**
     * ArrayList with path edges
     *
     * @var ArrayList
     */
    protected $edges;

    /**
     * ArrayList with path vertices
     *
     * @var ArrayList
     */
    protected $vertices;

    /**
     * Path constructor.
     *
     * @param array $edges Path edges
     * @param array $vertices Path vertices
     *
     * @throws InvalidParameterException|MissingParameterException
     */
    public function __construct(array $edges, array $vertices)
    {
        $this->edges = new ArrayList();
        $this->vertices = new ArrayList();

        foreach ($edges as $edge) {
            $this->edges->push(new Edge($edge));
        }

        foreach ($vertices as $vertex) {
            $this->vertices->push(new Vertex($vertex));
        }
    }

    /**
     * Return all the path edges
     *
     * @return ArrayList
     */
    public function getEdges(): ArrayList
    {
        return $this->edges;
    }

    /**
     * Return all the path vertices
     *
     * @return ArrayList
     */
    public function getVertices(): ArrayList
    {
        return $this->vertices;
    }
}
