<?php
declare(strict_types=1);

namespace ArangoDB\Graph\Traversal;

use ArangoDB\AQL\Statement;
use ArangoDB\Cursor\Cursor;
use ArangoDB\Document\Vertex;
use ArangoDB\Connection\Connection;
use ArangoDB\Cursor\TraversalCursor;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Cursor\Exceptions\CursorException;
use ArangoDB\AQL\Exceptions\StatementException;

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
     * The traversal query
     *
     * @var Statement
     */
    protected $statement;

    /**
     * Connection to use to manage database
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Cursor object
     *
     * @var Cursor
     */
    protected $cursor;

    /**
     * Traversal constructor.
     *
     * @param Statement $statement AQL Statement for traversal.
     * @param Connection|null $connection The connection object to use.
     *
     * @throws CursorException|GuzzleException
     */
    public function __construct(Statement $statement, Connection $connection = null)
    {
        $this->statement = $statement;
        $this->connection = $connection;

        if ($connection) {
            $this->cursor = new TraversalCursor($connection, $statement);
        }
    }

    /**
     * Sets a connection to use for traversal.
     *
     * @param Connection $connection
     */
    public function setConnection(Connection $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * Returns the query,
     *
     * @return string
     *
     * @throws StatementException
     */
    public function toAql()
    {
        return $this->statement->toAql();
    }

    /**
     * Returns a default traversal
     *
     * @param Vertex $vertex Vertex to start graph traversal.
     * @param string $graph The graph to traverse.
     * @param string $direction Direction for traversal.
     * @param int $depth Visits only nodes in at least the given depth.
     *
     * @return Traversal Traversal object.
     *
     * @throws CursorException|GuzzleException
     */
    public static function traversalQuery(Vertex $vertex, string $graph, string $direction = self::GRAPH_DIRECTION_ANY, int $depth = 0): Traversal
    {
        $query = sprintf("
                FOR v, e, p IN 1..%d %s
                '%s'
                GRAPH '%s'
                LIMIT 100
                RETURN p", $depth, $direction, $vertex->getId(), $graph);

        return new Traversal($vertex->getCollection()->getDatabase()->getConnection(), new Statement($query));
    }
}
