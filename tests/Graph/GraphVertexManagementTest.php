<?php
declare(strict_types=1);

namespace Unit\Graph;

use ArangoDB\Graph\Graph;
use ArangoDB\Document\Vertex;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Exceptions\Database\DatabaseException;

/**
 * Tests for graph vertex management
 *
 * @package Unit\Graph
 */
class GraphVertexManagementTest extends BaseGraphTest
{
    public function testGetVertexCollections()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);

        $graph->save();

        $vertexCollections = $graph->getVertexCollections();
        $this->assertInstanceOf(ArrayList::class, $vertexCollections);
        $this->assertCount(2, $vertexCollections);
        $this->assertTrue(in_array('coll_a', $vertexCollections->values()));
        $this->assertTrue(in_array('coll_b', $vertexCollections->values()));

        $this->assertTrue($graph->delete(true));
    }

    public function testGetVertexCollectionsForNewGraphs()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);

        $vertexCollections = $graph->getVertexCollections();
        $this->assertInstanceOf(ArrayList::class, $vertexCollections);
        $this->assertCount(0, $vertexCollections);
    }

    public function testGetVertexCollectionsThrowDatabaseExceptionOnNonDefinedDatabase()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Database not defined");
        $graph->getVertexCollections();
    }

    public function testGetVertexCollectionsThrowDatabaseException()
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
        $graph->getVertexCollections();
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

    public function testAddVertex()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $graph->save();

        $collA = $db->getCollection('coll_a');
        $this->assertEquals(0, $collA->count()); // 0 documents on vertex collection 'coll_a'

        $graph->addVertex('coll_a', ['_key' => 'Mama']);

        $this->assertEquals(1, $collA->count()); // 1 document on vertex collection 'coll_a'
        $this->assertTrue($graph->delete(true));
    }

    public function testAddVertexReturnFalseForNewGraphs()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);

        $this->assertFalse($graph->addVertex('coll_a', ['_key' => 'Mama']));
    }

    public function testAddVertexThrowDatabaseExceptionOnNonDefinedDatabase()
    {
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Database not defined");
        $graph->addVertex('coll_a', ['_key' => 'Mama']);
    }

    public function testAddVertexReturnFalseOnNonExistingGraphOrNonVertexCollection()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(404, [], json_encode($this->mockServerError()))
        ]);

        $db = $this->getConnectionObject($mock)->getDatabase();
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true), $db);

        $this->assertFalse($graph->addVertex('coll_a', ['_key' => 'Mama']));
    }

    public function testAddVertexThrowDatabaseException()
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
        $this->assertFalse($graph->addVertex('coll_a', ['_key' => 'Mama']));
    }

    public function testGetVertex()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $graph->save();
        $graph->addVertex('coll_a', ['_key' => 'Mama', 'attr' => 'feel_the_noise']);

        $vertex = $graph->getVertex('coll_a', 'Mama');
        $this->assertInstanceOf(Vertex::class, $vertex);
        $this->assertEquals('feel_the_noise', $vertex->attr);
        $this->assertNotNull($vertex->getCollection());

        $this->assertTrue($graph->delete(true));
    }

    public function testGetVertexReturnFalseForNewGraphs()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);

        $this->assertFalse($graph->getVertex('coll_a', 'feel_the_noise'));
    }

    public function testGetVertexThrowDatabaseExceptionOnNonDefinedDatabase()
    {
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Database not defined");
        $vertex = $graph->getVertex('coll_a', 'bang_your_head');
    }

    public function testGetVertexReturnFalseOnNonExistingVertex()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $graph->save();
        $graph->addVertex('coll_a', ['_key' => 'Mama', 'attr' => 'feel_the_noise']);

        $vertex = $graph->getVertex('coll_a', 'Papa');
        $this->assertFalse($vertex);

        $this->assertTrue($graph->delete(true));
    }

    public function testGetVertexThrowDatabaseException()
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
        $vertex = $graph->getVertex('coll_a', 'Papa');
    }

    public function testDropVertex()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $graph->save();

        $collA = $db->getCollection('coll_a');
        $graph->addVertex('coll_a', ['_key' => 'Mama']);
        $this->assertEquals(1, $collA->count()); // 1 document on vertex collection 'coll_a'

        $graph->dropVertex('coll_a', 'Mama');

        $this->assertEquals(0, $collA->count()); // 0 documents on vertex collection 'coll_a'
        $this->assertTrue($graph->delete(true));
    }

    public function testDropVertexReturnFalseForNewGraphs()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);

        $this->assertFalse($graph->dropVertex('coll_a', 'Mama'));
    }

    public function testDropVertexThrowDatabaseExceptionOnNonDefinedDatabase()
    {
        $graph = new Graph("my_graph", $this->mockGraphAttributes(true));

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("Database not defined");
        $graph->dropVertex('coll_a', 'Mama');
    }

    public function testDropVertexThrowDatabaseException()
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
        $graph->dropVertex('any_vertex_coll', 'any_vertex');
    }


    public function testDropVertexReturnFalseOnNonExistingGraphOrNonVertexCollection()
    {
        $db = $this->getConnectionObject()->getDatabase();

        $graph = new Graph("my_graph", $this->mockGraphAttributes(), $db);
        $graph->save();

        $vertex = $graph->dropVertex('coll_a', 'Papa');
        $this->assertFalse($vertex);

        $this->assertTrue($graph->delete(true));
    }
}
