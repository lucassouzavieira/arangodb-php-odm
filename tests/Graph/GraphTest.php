<?php


namespace Unit\Graph;

use ArangoDB\Graph\Graph;
use ArangoDB\Document\Vertex;
use GuzzleHttp\Psr7\Response;
use ArangoDB\Graph\EdgeDefinition;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Collection\Collection;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Graph\Traversal\Traversal;
use ArangoDB\Exceptions\DatabaseException;
use ArangoDB\Exceptions\Exception as ArangoException;

/**
 * Tests for graph
 *
 * @package Unit\Graph
 */
class GraphTest extends BaseGraphTest
{
    public function testConstructorOnlyWithName()
    {
        $graph = new Graph('my_graph');
        $this->assertTrue($graph->isNew());
        $this->assertFalse($graph->isSmart());
        $this->assertEquals('my_graph', $graph->getName());
        $this->assertEquals('my_graph', $graph->getKey());
        $this->assertEquals('', $graph->getId());
        $this->assertInstanceOf(ArrayList::class, $graph->getEdgeDefinitions());
        $this->assertCount(0, $graph->getEdgeDefinitions());
    }

    public function testConstructorWithAttributesWithoutDescriptors()
    {
        // Without descriptors
        $graph = new Graph('my_graph', $this->mockGraphAttributes());
        $this->assertTrue($graph->isNew());
        $this->assertFalse($graph->isSmart());
        $this->assertEquals('my_graph', $graph->getName());
        $this->assertEquals('my_graph', $graph->getKey());
        $this->assertEquals('', $graph->getId());
        $this->assertInstanceOf(ArrayList::class, $graph->getEdgeDefinitions());
        $this->assertCount(1, $graph->getEdgeDefinitions());
    }

    public function testConstructorWithAttributesWithDescriptors()
    {
        // With descriptors
        $graph = new Graph('my_graph', $this->mockGraphAttributes(true));
        $this->assertFalse($graph->isNew());
        $this->assertFalse($graph->isSmart());
        $this->assertEquals('my_graph', $graph->getName());
        $this->assertEquals('my_graph', $graph->getKey());
        $this->assertEquals('_graphs/mygraph', $graph->getId());
        $this->assertInstanceOf(ArrayList::class, $graph->getEdgeDefinitions());
        $this->assertCount(1, $graph->getEdgeDefinitions());
    }

    public function testConstructorWithAttributesWithArrayListForEdgesDefinitions()
    {
        // With descriptors
        $attributes = $this->mockGraphAttributes(true);
        $attributes['edgeDefinitions'] = new ArrayList([new EdgeDefinition('someEdgeColl', ['coll_a'], ['coll_b'])]);

        $graph = new Graph('my_graph', $attributes);
        $this->assertFalse($graph->isNew());
        $this->assertFalse($graph->isSmart());
        $this->assertEquals('my_graph', $graph->getName());
        $this->assertEquals('my_graph', $graph->getKey());
        $this->assertEquals('_graphs/mygraph', $graph->getId());
        $this->assertInstanceOf(ArrayList::class, $graph->getEdgeDefinitions());
        $this->assertCount(1, $graph->getEdgeDefinitions());
    }

    public function testIsNew()
    {
        $graph = new Graph('mygraph');
        $this->assertTrue($graph->isNew());

        $graph = new Graph('mygraph', $this->mockGraphAttributes(true));
        $this->assertFalse($graph->isNew());
    }

    public function testIsSmart()
    {
        $graph = new Graph('mygraph');
        $this->assertFalse($graph->isSmart());

        $attributes = $this->mockGraphAttributes(true);
        $attributes['isSmart'] = true;

        $graph = new Graph('mygraph', $attributes);
        $this->assertTrue($graph->isSmart());
    }

    public function testGetRevision()
    {
        $graph = new Graph('mygraph');
        $this->assertEquals(0, strlen($graph->getRevision()));

        $attributes = $this->mockGraphAttributes(true);
        $graph = new Graph('mygraph', $attributes);
        $this->assertGreaterThan(0, strlen($graph->getRevision()));
    }

    public function testGetNumberOfShards()
    {
        $graph = new Graph('mygraph');
        $this->assertEquals(1, $graph->getNumberOfShards());

        $attributes = $this->mockGraphAttributes(true);
        $attributes['numberOfShards'] = 2;

        $graph = new Graph('mygraph', $attributes);
        $this->assertEquals(2, $graph->getNumberOfShards());
    }

    public function testGetReplicationFactor()
    {
        $graph = new Graph('mygraph');
        $this->assertEquals(1, $graph->getReplicationFactor());

        $attributes = $this->mockGraphAttributes(true);
        $attributes['replicationFactor'] = 2;

        $graph = new Graph('mygraph', $attributes);
        $this->assertEquals(2, $graph->getReplicationFactor());
    }

    public function testGetMinReplicationFactor()
    {
        $graph = new Graph('mygraph');
        $this->assertEquals(1, $graph->getMinReplicationFactor());

        $attributes = $this->mockGraphAttributes(true);
        $attributes['minReplicationFactor'] = 2;

        $graph = new Graph('mygraph', $attributes);
        $this->assertEquals(2, $graph->getMinReplicationFactor());
    }

    public function testGetOrphanCollections()
    {
        $graph = new Graph('mygraph');
        $this->assertIsArray($graph->getOrphanCollections());
        $this->assertCount(0, $graph->getOrphanCollections());

        $attributes = $this->mockGraphAttributes(true);
        $attributes['orphanCollections'] = ['orphan'];

        $graph = new Graph('mygraph', $attributes);
        $this->assertIsArray($graph->getOrphanCollections());
        $this->assertCount(1, $graph->getOrphanCollections());
        $this->assertTrue(in_array('orphan', $graph->getOrphanCollections()));
    }

    public function testTraversal()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", ['edgeDefinitions' => [$this->mockEdgeDefinitions()]], $db);
        $traversal = $graph->traversal(new Vertex(), Traversal::GRAPH_DIRECTION_ANY, 2);

        $this->assertInstanceOf(Traversal::class, $traversal);
    }

    public function testSave()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collA = new Collection("coll_a", $db);
        $collB = new Collection("coll_b", $db);

        $collA->save();
        $collB->save();

        $graph = new Graph("my_graph", ['edgeDefinitions' => [$this->mockEdgeDefinitions()]], $db);
        $this->assertTrue($graph->save());
        $this->assertFalse($graph->isNew());
        $this->assertGreaterThan(0, strlen($graph->getId()));
        $this->assertGreaterThan(0, strlen($graph->getRevision()));
        $this->assertTrue($graph->delete());
    }

    public function testSaveReturnFalse()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collA = new Collection("coll_a", $db);
        $collB = new Collection("coll_b", $db);

        $collA->save();
        $collB->save();

        $graph = new Graph("my_graph", ['edgeDefinitions' => [$this->mockEdgeDefinitions()]], $db);
        $this->assertTrue($graph->save());

        $dbGraphs = $db->getAllGraphs();
        $first = $dbGraphs->first();

        $this->assertFalse($first->isNew());
        $this->assertFalse($first->save()); // Old graphs cannot be created again.
        $this->assertTrue($graph->delete());
    }

    public function testSaveThrowException()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collA = new Collection("coll_a", $db);
        $collB = new Collection("coll_b", $db);

        $collA->save();
        $collB->save();

        $graph = new Graph("my_graph", [], $db);
        $this->expectException(ArangoException::class);
        $this->expectExceptionMessage("Edges definitions are missing");
        $graph->save();
    }

    public function testSaveThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = $this->getConnectionObject($mock)->getDatabase();
        $graph = new Graph("my_graph", ['edgeDefinitions' => [$this->mockEdgeDefinitions()]], $db);
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Mocked error");
        $graph->save();
    }

    public function testSaveThrowDatabaseExceptionOnNotDefinedDatabase()
    {
        $graph = new Graph("my_graph");
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Database not defined");
        $graph->save();
    }

    public function testDelete()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collA = new Collection("coll_a", $db);
        $collB = new Collection("coll_b", $db);
        $edgeColl = new Collection("edge_coll", $db, ['type' => 3]);

        $collA->save();
        $collB->save();
        $edgeColl->save();

        // Our database starts with 0 graphs
        $graphList = $db->getAllGraphs();
        $this->assertCount(0, $graphList);

        $graph = new Graph("my_graph", ['edgeDefinitions' => [$this->mockEdgeDefinitions()]], $db);
        $this->assertTrue($graph->save());

        // Now must have 1 graph
        $graphList = $db->getAllGraphs();
        $this->assertCount(1, $graphList);


        $this->assertTrue($graph->delete());

        // And now must have 0 graphs again
        $graphList = $db->getAllGraphs();
        $this->assertCount(0, $graphList);
    }

    public function testDeleteReturnFalse()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collA = new Collection("coll_a", $db);
        $collB = new Collection("coll_b", $db);

        $collA->save();
        $collB->save();

        $graph = new Graph("my_graph", ['edgeDefinitions' => [$this->mockEdgeDefinitions()]], $db);
        $this->assertFalse($graph->delete());
    }

    public function testDeleteThrowException()
    {
        $graph = new Graph("my_graph", []);
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Database not defined");
        $graph->delete();
    }

    public function testDeleteThrowDatabaseException()
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
        $graph->delete();
    }

    public function testDeleteDropCollections()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collA = new Collection("coll_a", $db);
        $collB = new Collection("coll_b", $db);

        $collA->save();
        $collB->save();

        // Our database starts with 0 graphs
        $graphList = $db->getAllGraphs();
        $this->assertCount(0, $graphList);

        // And 2 previously created collections
        $collections = $db->getAllCollections();
        $this->assertCount(2, $collections);

        // Create graph
        $graph = new Graph("my_graph", ['edgeDefinitions' => [$this->mockEdgeDefinitions()]], $db);
        $this->assertTrue($graph->save());

        // Expects  collections: 'coll_a', 'coll_b' and 'edge_coll'
        $graphList = $db->getAllCollections();
        $this->assertCount(3, $graphList);

        // And 1 graph
        $graphList = $db->getAllGraphs();
        $this->assertCount(1, $graphList);

        $this->assertTrue($graph->delete(true));

        // And now must have 0 graphs again
        $graphList = $db->getAllGraphs();
        $this->assertCount(0, $graphList);

        // And 0 collections
        $collections = $db->getAllCollections();
        $this->assertCount(0, $collections);
    }

    public function testJsonSerialize()
    {
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));
        $this->assertJson(json_encode($graph));
    }

    public function testToArray()
    {
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));
        $arr = $graph->toArray();
        $this->assertIsArray($arr);
        $this->assertArrayHasKey('_id', $arr);
        $this->assertArrayHasKey('_key', $arr);
        $this->assertArrayHasKey('_rev', $arr);
        $this->assertArrayHasKey('name', $arr);
        $this->assertArrayHasKey('edgeDefinitions', $arr);
    }

    public function testToString()
    {
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));
        $str = (string)$graph;
        $this->assertStringContainsString('_id', $str);
        $this->assertStringContainsString('_key', $str);
        $this->assertStringContainsString('_rev', $str);
        $this->assertStringContainsString('name', $str);
        $this->assertStringContainsString('edgeDefinitions', $str);
    }
}
