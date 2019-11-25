<?php

namespace Unit\Cursor;

use ArangoDB\AQL\Statement;
use ArangoDB\Document\Edge;
use ArangoDB\Document\Vertex;
use GuzzleHttp\Psr7\Response;
use ArangoDB\Graph\Traversal\Path;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Cursor\TraversalCursor;
use Unit\Graph\Utils\MockGraphTrait;
use ArangoDB\Cursor\Exceptions\CursorException;

class TraversalCursorTest extends CursorTestCase
{
    use MockGraphTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockGraph($this->getConnectionObject()->getDatabase());
    }

    public function tearDown(): void
    {
        $db = $this->getConnectionObject()->getDatabase();
        $db->getGraph('traversal_test_graph')->delete(true);
        parent::tearDown();
    }

    public function testConstructorThrowCursorException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $statement = "
            FOR v, e, p IN 1..@depth OUTBOUND
            @start
            GRAPH @graph
            RETURN p
        ";

        $statement = new Statement($statement);
        $statement->bindValue("@depth", 1);
        $statement->bindValue("@start", 'cities/itz');
        $statement->bindValue("@graph", 'traversal_test_graph');

        $connection = $this->getConnectionObject($mock);

        $this->expectException(CursorException::class);
        $cursor = new TraversalCursor($connection, $statement);
    }

    public function testConstructorQueryRequestPaths()
    {
        $statement = "
            FOR v, e, p IN 1..@depth ANY
            @start
            GRAPH @graph
            RETURN p
        ";

        $statement = new Statement($statement);
        $statement->bindValue("@depth", 1);
        $statement->bindValue("@start", 'cities/itz');
        $statement->bindValue("@graph", 'traversal_test_graph');

        $connection = $this->getConnectionObject();
        $connection->getDatabase()->createCollection('test_cursor_coll');

        $cursor = new TraversalCursor($connection, $statement);
        $this->assertIsIterable($cursor);
        $current = $cursor->current();

        $this->assertInstanceOf(Path::class, $current);
    }

    public function testConstructorQueryRequestEdges()
    {
        $statement = "
            FOR v, e, p IN 1..@depth ANY
            @start
            GRAPH @graph
            RETURN e
        ";

        $statement = new Statement($statement);
        $statement->bindValue("@depth", 1);
        $statement->bindValue("@start", 'cities/itz');
        $statement->bindValue("@graph", 'traversal_test_graph');

        $connection = $this->getConnectionObject();
        $connection->getDatabase()->createCollection('test_cursor_coll');

        $cursor = new TraversalCursor($connection, $statement);
        $this->assertIsIterable($cursor);
        $current = $cursor->current();

        $this->assertInstanceOf(Edge::class, $current);
    }

    public function testConstructorQueryRequestVertices()
    {
        $statement = "
            FOR v, e, p IN 1..@depth ANY
            @start
            GRAPH @graph
            RETURN v
        ";

        $statement = new Statement($statement);
        $statement->bindValue("@depth", 1);
        $statement->bindValue("@start", 'cities/itz');
        $statement->bindValue("@graph", 'traversal_test_graph');

        $connection = $this->getConnectionObject();
        $connection->getDatabase()->createCollection('test_cursor_coll');

        $cursor = new TraversalCursor($connection, $statement);
        $this->assertIsIterable($cursor);
        $current = $cursor->current();

        $this->assertInstanceOf(Vertex::class, $current);
    }
}
