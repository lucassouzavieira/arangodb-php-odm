<?php
declare(strict_types=1);

namespace ArangoDB\Cursor;

use ArangoDB\Document\Edge;
use ArangoDB\Document\Vertex;
use ArangoDB\Graph\Traversal\Path;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Represents an ArangoDB cursor for a Traversal query
 *
 * @package ArangoDB\Cursor
 * @author Lucas S. Vieira
 */
class TraversalCursor extends Cursor
{
    /**
     * May return a path, a edge or a vertex. This is defined on statement.
     * Non traversal queries will return all entries as vertex objects.
     *
     * @return Edge|Vertex|Path The object to be returned.
     *
     * @throws InvalidParameterException|MissingParameterException
     */
    public function current(): mixed
    {
        $current = $this->result->get($this->position);

        // The statement request the edges.
        if (isset($current['_from']) && isset($current['_to'])) {
            return new Edge($current);
        }

        // The statement request the paths.
        if (isset($current['edges']) && isset($current['vertices'])) {
            return new Path($current['edges'], $current['vertices']);
        }

        // The default case is when the statement request the vertices.
        return new Vertex($current);
    }
}
