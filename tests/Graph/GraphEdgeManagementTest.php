<?php


namespace Unit\Graph;

use ArangoDB\Graph\Graph;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Exceptions\DatabaseException;

class GraphEdgeManagementTest extends BaseGraphTest
{
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
}
