<?php


namespace Unit\Graph;

use ArangoDB\Graph\Graph;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Exceptions\DatabaseException;

/**
 * Tests for graph vertex management
 *
 * @package Unit\Graph
 */
class GraphVertexManagementTest extends BaseGraphTest
{
    public function testGetVertexesCollections()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);

        $graph->save();

        $vertexesCollections = $graph->getVertexesCollections();
        $this->assertInstanceOf(ArrayList::class, $vertexesCollections);
        $this->assertCount(2, $vertexesCollections);
        $this->assertTrue(in_array('coll_a', $vertexesCollections->values()));
        $this->assertTrue(in_array('coll_b', $vertexesCollections->values()));

        $this->assertTrue($graph->delete(true));
    }

    public function testGetVertexesCollectionsForNewGraphs()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);

        $vertexesCollections = $graph->getVertexesCollections();
        $this->assertInstanceOf(ArrayList::class, $vertexesCollections);
        $this->assertCount(0, $vertexesCollections);
    }

    public function testGetVertexesCollectionsThrowDatabaseExceptionOnNonDefinedDatabase()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Database not defined");
        $graph->getVertexesCollections();
    }

    public function testGetVertexesThrowDatabaseException()
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
        $graph->getVertexesCollections();
    }

    public function testAddVertexCollection()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);

        $graph->save();
        $graph->addVertexCollection('vertex_coll');

        $graphObj = $db->getGraph('my_graph');
        $this->assertCount(1, $graph->getOrphanCollections());
        $this->assertContains('vertex_coll', $graph->getOrphanCollections());
        $this->assertTrue($graph->delete(true));
    }

    public function testAddVertexCollectionReturnTrueForNewGraphs()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);

        $this->assertCount(0, $graph->getOrphanCollections());

        $this->assertTrue($graph->addVertexCollection('vertex_coll'));

        $this->assertCount(1, $graph->getOrphanCollections());
        $this->assertContains('vertex_coll', $graph->getOrphanCollections());
    }

    public function testAddVertexCollectionThrowDatabaseExceptionOnNonDefinedDatabase()
    {
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Database not defined");
        $graph->addVertexCollection('vertex_coll');
    }

    public function testAddVertexCollectionThrowDatabaseException()
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
        $graph->addVertexCollection('vertex_coll');
    }

    public function testDropVertexCollection()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);

        $graph->save();
        $graph->addVertexCollection('vertex_coll_a');
        $graph->addVertexCollection('vertex_coll_b');

        $this->assertCount(2, $graph->getOrphanCollections());
        $this->assertContains('vertex_coll_a', $graph->getOrphanCollections());
        $this->assertContains('vertex_coll_b', $graph->getOrphanCollections());

        $graph->dropVertexCollection('vertex_coll_a');

        $graphObj = $db->getGraph('my_graph');
        $this->assertCount(1, $graphObj->getOrphanCollections());
        $this->assertContains('vertex_coll_b', $graphObj->getOrphanCollections());
        $this->assertTrue($db->hasCollection('vertex_coll_a')); // Collection was not dropped

        $this->assertTrue($graph->delete(true));
        $this->assertTrue($db->dropCollection('vertex_coll_a'));
    }

    public function testDropVertexCollectionDroppingCollection()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);

        $graph->save();
        $graph->addVertexCollection('vertex_coll_a');
        $graph->addVertexCollection('vertex_coll_b');

        $this->assertCount(2, $graph->getOrphanCollections());
        $this->assertContains('vertex_coll_a', $graph->getOrphanCollections());
        $this->assertContains('vertex_coll_b', $graph->getOrphanCollections());

        $graph->dropVertexCollection('vertex_coll_a', true);

        $graphObj = $db->getGraph('my_graph');
        $this->assertCount(1, $graphObj->getOrphanCollections());
        $this->assertContains('vertex_coll_b', $graphObj->getOrphanCollections());
        $this->assertFalse($db->hasCollection('vertex_coll_a')); // Collection was dropped

        $this->assertTrue($graph->delete(true));
    }

    public function testDropVertexCollectionReturnFalseForNewGraphs()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $this->assertFalse($graph->dropVertexCollection('any_vertex_coll'));
    }

    public function testDropVertexCollectionThrowDatabaseExceptionOnNonDefinedDatabase()
    {
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Database not defined");
        $graph->dropVertexCollection('any_vertex_coll');
    }

    public function testDropVertexCollectionThrowDatabaseException()
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
        $graph->dropVertexCollection('vertex_coll');
    }
}
