<?php


namespace Unit\Graph;

use ArangoDB\Document\Edge;
use ArangoDB\Graph\Graph;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Exceptions\DatabaseException;

/**
 * Tests for graph edge management
 *
 * @package Unit\Graph
 */
class GraphEdgeManagementTest extends BaseGraphTest
{
    public function tearDown(): void
    {
        if ($this->getConnectionObject()->getDatabase()->getGraph('test_graph')) {
            $this->getConnectionObject()->getDatabase()->getGraph('test_graph')->delete(true);
        }

        parent::tearDown();
    }

    public function testAddEdgeDefinition()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);

        $graph->save();

        $this->assertCount(1, $graph->getEdgeDefinitions());
        $graph->addEdgeDefinition('edgeCollB', ['coll_x'], ['coll_y']);

        $graphObj = $db->getGraph('my_graph');
        $this->assertCount(2, $graphObj->getEdgeDefinitions());
        $this->assertTrue($graphObj->delete(true));
    }

    public function testAddEdgeDefinitionThrowDatabaseExceptionOnNonDefinedDatabase()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Database not defined");
        $graph->addEdgeDefinition('edge_coll', ['coll_c'], ['coll_d']);
    }


    public function testAddEdgeDefinitionThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = $this->getConnectionObject($mock)->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true), $db);

        $this->expectException(DatabaseException::class);
        $graph->addEdgeDefinition('edge_coll', ['coll_c'], ['coll_d']);
    }

    public function testAddEdgeDefinitionToNewGraph()
    {
        $graph = new Graph("my_graph", $this->mockGraphAttributes());

        $this->assertCount(1, $graph->getEdgeDefinitions());

        $graph->addEdgeDefinition('edgeCollB', ['coll_x'], ['coll_y']);
        $this->assertCount(2, $graph->getEdgeDefinitions());
    }

    public function testDropEdgeDefinition()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);

        $graph->addEdgeDefinition('edge_coll_b', ['coll_x'], ['coll_y']);
        $graph->save();
        $this->assertCount(2, $graph->getEdgeDefinitions());

        $this->assertTrue($graph->dropEdgeDefinition('edge_coll_b'));

        $graphObj = $db->getGraph('my_graph');
        $this->assertCount(1, $graphObj->getEdgeDefinitions());
        $this->assertTrue($graphObj->delete(true));
    }

    public function testDropEdgeDefinitionReturnFalseForNonExistentEdgeCollection()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $graph->save();

        $this->assertFalse($graph->dropEdgeDefinition('random_edge_coll'));

        $this->assertTrue($graph->delete(true));
    }

    public function testDropEdgeDefinitionReturnFalseForNewGraphs()
    {
        $graph = new Graph("my_graph", $this->mockGraphAttributes());
        $graph->addEdgeDefinition('edge_coll_b', ['coll_x'], ['coll_y']);

        $this->assertFalse($graph->dropEdgeDefinition('edge_coll_b'));
    }

    public function testDropEdgeDefinitionThrowDatabaseExceptionOnNonDefinedDatabase()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Database not defined");
        $graph->dropEdgeDefinition('any_edge_coll');
    }

    public function testDropEdgeDefinitionThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = $this->getConnectionObject($mock)->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true), $db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Mocked error");

        $graph->dropEdgeDefinition('any_edge_coll');
    }

    public function testAddEdge()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $graph->save();

        $edgeCollection = $db->getCollection('someEdgeColl');
        $this->assertEquals(0, $edgeCollection->count());

        $this->assertTrue($graph->addVertex('coll_a', ['_key' => 'mama']));
        $this->assertTrue($graph->addVertex('coll_b', ['_key' => 'papa']));

        $attributes = [
            '_from' => 'coll_a/mama',
            '_to' => 'coll_b/papa',
            'status' => 'married'
        ];

        $this->assertTrue($graph->addEdge('someEdgeColl', $attributes));
        $this->assertEquals(1, $edgeCollection->count());
        $this->assertTrue($graph->delete(true));
    }

    public function testAddEdgeReturnFalseForNewGraphs()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $attributes = [
            '_from' => 'coll_a/mama',
            '_to' => 'coll_b/papa',
            'status' => 'married'
        ];

        $this->assertFalse($graph->addEdge('someEdgeColl', $attributes));
    }

    public function testAddEdgeThrowDatabaseExceptionOnNonDefinedDatabase()
    {
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));
        $attributes = [
            '_from' => 'coll_a/mama',
            '_to' => 'coll_b/papa',
            'status' => 'married'
        ];

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Database not defined");
        $graph->addEdge('coll_a', $attributes);
    }

    public function testAddEdgeReturnFalseOnNonExistingGraphOrNonVertexCollection()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $graph->save();

        $edgeCollection = $db->getCollection('someEdgeColl');
        $this->assertEquals(0, $edgeCollection->count());

        $this->assertTrue($graph->addVertex('coll_a', ['_key' => 'mama']));
        $this->assertTrue($graph->addVertex('coll_b', ['_key' => 'papa']));

        $attributes = [
            '_from' => 'coll_a/mama',
            '_to' => 'coll_b/aunt',
        ];

        $this->assertFalse($graph->addEdge('someEdgeColl', $attributes));
        $this->assertEquals(0, $edgeCollection->count());
        $this->assertTrue($graph->delete(true));
    }

    public function testAddEdgeThrowDatabaseExceptionOnMissingRequiredAttribute()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("test_graph", $this->mockGraphAttributes(), $db);
        $graph->save();

        $edgeCollection = $db->getCollection('someEdgeColl');
        $this->assertEquals(0, $edgeCollection->count());

        $this->assertTrue($graph->addVertex('coll_a', ['_key' => 'mama']));
        $this->assertTrue($graph->addVertex('coll_b', ['_key' => 'papa']));

        $attributes = [
            '_from' => 'coll_a/mama',
        ];

        $this->expectException(DatabaseException::class);
        $graph->addEdge('someEdgeColl', $attributes);
    }

    public function testGetEdge()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $graph->save();

        $edgeCollection = $db->getCollection('someEdgeColl');
        $this->assertEquals(0, $edgeCollection->count());

        $this->assertTrue($graph->addVertex('coll_a', ['_key' => 'mama']));
        $this->assertTrue($graph->addVertex('coll_b', ['_key' => 'papa']));

        $attributes = [
            '_from' => 'coll_a/mama',
            '_to' => 'coll_b/papa',
            '_key' => 'married',
            'attr' => 'active'
        ];

        $this->assertTrue($graph->addEdge('someEdgeColl', $attributes));

        $edge = $graph->getEdge('someEdgeColl', 'married');
        $this->assertInstanceOf(Edge::class, $edge);
        $this->assertEquals('active', $edge->attr);

        $this->assertTrue($graph->delete(true));
    }

    public function testGetEdgeReturnFalseForNonExistingEdges()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $graph->save();

        $edgeCollection = $db->getCollection('someEdgeColl');
        $this->assertEquals(0, $edgeCollection->count());

        $this->assertTrue($graph->addVertex('coll_a', ['_key' => 'mama']));
        $this->assertTrue($graph->addVertex('coll_b', ['_key' => 'papa']));

        $attributes = [
            '_from' => 'coll_a/mama',
            '_to' => 'coll_b/papa',
            '_key' => 'married',
            'attr' => 'active'
        ];

        $this->assertTrue($graph->addEdge('someEdgeColl', $attributes));

        $edge = $graph->getEdge('someEdgeColl', 'non_existent');
        $this->assertFalse($edge);

        $this->assertTrue($graph->delete(true));
    }

    public function testGetEdgeReturnFalseForNewGraphs()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $this->assertFalse($graph->getEdge('someEdgeColl', 'edgeKey'));
    }

    public function testGetEdgeThrowDatabaseExceptionOnNonDefinedDatabase()
    {
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Database not defined");
        $graph->getEdge('coll_a', 'any');
    }

    public function testGetEdgeReturnFalseOnNonExistingGraphOrNonVertexCollection()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = $this->getConnectionObject($mock)->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true), $db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Mocked error");
        $graph->getEdge('any_edge_coll', 'any_edge');
    }

    public function testDropEdge()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $graph->save();

        $edgeCollection = $db->getCollection('someEdgeColl');

        $this->assertTrue($graph->addVertex('coll_a', ['_key' => 'mama']));
        $this->assertTrue($graph->addVertex('coll_b', ['_key' => 'papa']));

        $attributes = [
            '_from' => 'coll_a/mama',
            '_to' => 'coll_b/papa',
            '_key' => 'married'
        ];

        $this->assertTrue($graph->addEdge('someEdgeColl', $attributes));
        $this->assertEquals(1, $edgeCollection->count());

        $this->assertTrue($graph->dropEdge('someEdgeColl', 'married'));

        $this->assertEquals(0, $edgeCollection->count());
        $this->assertTrue($graph->delete(true));
    }

    public function testDropEdgeReturnFalseForNewGraphs()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $this->assertFalse($graph->dropEdge('someEdgeColl', 'any_edge'));
    }

    public function testDropEdgeThrowDatabaseExceptionOnNonDefinedDatabase()
    {
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Database not defined");
        $graph->dropEdge('someEdgeColl', 'any_edge');
    }

    public function testDropEdgeReturnFalseOnNonExistingGraphOrNonVertexCollection()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(true), $db);
        $this->assertFalse($graph->dropEdge('someEdgeColl', 'any_edge'));
    }

    public function testDropEdgeThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = $this->getConnectionObject($mock)->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true), $db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Mocked error");
        $graph->dropEdge('any_edge_coll', 'any_edge');
    }
}
