<?php


namespace Unit\Database;

use Unit\TestCase;
use ArangoDB\Graph\Graph;
use GuzzleHttp\Psr7\Response;
use ArangoDB\Database\Database;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Collection\Collection;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Exceptions\DatabaseException;

class DatabaseTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testGetDatabaseName()
    {
        $db = new Database($this->getConnectionObject());
        $this->assertEquals(getenv('ARANGODB_DBNAME'), $db->getDatabaseName());
    }

    public function testGetInfo()
    {
        $db = new Database($this->getConnectionObject());
        $info = $db->getInfo();
        $this->assertIsArray($db->getInfo());
        $this->assertEquals($info['name'], getenv('ARANGODB_DBNAME'));
    }

    public function testCreateCollection()
    {
        $db = new Database($this->getConnectionObject());
        $collection = $db->createCollection('my_new_collection');

        // Check if collection is created.
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertNotEmpty($collection->getGloballyUniqueId());
        $this->assertTrue($collection->drop());
    }

    public function testGetAllCollections()
    {
        $db = new Database($this->getConnectionObject());
        $db->createCollection('coll_a');
        $db->createCollection('coll_b');

        // Check if collections are created.
        $collections = $db->getAllCollections();
        $this->assertInstanceOf(ArrayList::class, $collections);
        $this->assertCount(2, $collections);

        $arr = $collections->toArray();

        $this->assertInstanceOf(Collection::class, $arr[0]);
        $this->assertInstanceOf(Collection::class, $arr[1]);

        // Removes
        $this->assertTrue($db->dropCollection('coll_a'));
        $this->assertTrue($db->dropCollection('coll_b'));
    }

    public function testGetAllGraphs()
    {
        $db = new Database($this->getConnectionObject());

        $graphs = $db->getAllGraphs();
        $this->assertInstanceOf(ArrayList::class, $graphs);
        $this->assertCount(0, $graphs);
    }

    public function testGetGraphWithServerResponse()
    {
        $mockGraph = [
            '_id' => '_graphs/mygraph',
            '_key' => 'mygraph',
            '_rev' => '--zGahsoet1',
            'numberOfShards' => 1,
            'replicationFactor' => 1,
            'minReplicationFactor' => 1,
            'isSmart' => false,
            'edgeDefinitions' => [
                [
                    'collection' => 'someEdgeColl',
                    'from' => [
                        'coll_a',
                    ],
                    'to' => [
                        'coll_b'
                    ]
                ]
            ],
            'orphanCollections' => []
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['error' => false, 'code' => 200, 'graph' => $mockGraph])),
        ]);

        $db = new Database($this->getConnectionObject($mock));

        $graph = $db->getGraph("mygraph");
        $this->assertInstanceOf(Graph::class, $graph);
        $this->assertFalse($graph->isNew());
    }

    public function testGetGraphReturnFalse()
    {
        $db = new Database($this->getConnectionObject());

        $graph = $db->getGraph("nonExistingGraph");
        $this->assertFalse($graph);
    }

    public function testGetGraphThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError())),
        ]);

        $db = new Database($this->getConnectionObject($mock));

        $this->expectException(DatabaseException::class);
        $graph = $db->getGraph("mygraph");
    }

    public function testGetAllGraphsWithServerResponse()
    {
        $mockGraph = [
            '_id' => '_graphs/mygraph',
            '_key' => 'mygraph',
            '_rev' => '--zGahsoet1',
            'numberOfShards' => 1,
            'replicationFactor' => 1,
            'minReplicationFactor' => 1,
            'isSmart' => false,
            'edgeDefinitions' => [
                [
                    'collection' => 'someEdgeColl',
                    'from' => [
                        'coll_a',
                    ],
                    'to' => [
                        'coll_b'
                    ]
                ]
            ],
            'orphanCollections' => []
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['error' => false, 'code' => 200, 'graphs' => [$mockGraph]])),
        ]);

        $db = new Database($this->getConnectionObject($mock));

        $graphs = $db->getAllGraphs();
        $this->assertInstanceOf(ArrayList::class, $graphs);
        $this->assertCount(1, $graphs);
        $this->assertInstanceOf(Graph::class, $graphs->first());
        $this->assertFalse($graphs->first()->isNew());
    }

    public function testGetAllGraphsThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = new Database($this->getConnectionObject($mock));
        $this->expectException(DatabaseException::class);
        $db->getAllGraphs();
    }

    public function testGetCollection()
    {
        $db = new Database($this->getConnectionObject());
        $db->createCollection('my_new_collection');

        $collection = $db->getCollection('my_new_collection');
        // Check if collection was recovered.
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertNotEmpty($collection->getGloballyUniqueId());

        $this->assertTrue($collection->drop());
    }

    public function testGetNonExistentCollection()
    {
        $db = new Database($this->getConnectionObject());

        $collection = $db->getCollection('my_new_collection');
        $this->assertFalse($collection);
    }

    public function testDropCollection()
    {
        $db = new Database($this->getConnectionObject());
        $collection = $db->createCollection('my_new_collection');

        // Create.
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertNotEmpty($collection->getGloballyUniqueId());

        // Check exclusion
        $this->assertTrue($db->dropCollection('my_new_collection'));
        $newDbObj = new Database($this->getConnectionObject());
        $this->assertFalse($newDbObj->hasCollection('my_new_collection'));
    }

    public function testDropCollectionThrowDatabaseException()
    {
        $db = new Database($this->getConnectionObject());

        // Mock error
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $dbObj = new Database($this->getConnectionObject($mock));
        $this->expectException(DatabaseException::class);
        $dbObj->dropCollection('my_new_collection');
        $this->assertTrue($db->dropCollection('my_new_colletion'));
    }

    public function testDropNonExistentCollection()
    {
        $db = new Database($this->getConnectionObject());

        // Check exclusion
        $this->assertFalse($db->dropCollection('random_collection'));
    }

    public function testHasCollectionThrowDatabaseException()
    {
        // Mock error
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $dbObj = new Database($this->getConnectionObject($mock));
        $this->expectException(DatabaseException::class);
        $dbObj->hasCollection('my_new_collection');
    }

    public function testGetCollectionThrowDatabaseException()
    {
        // Mock error
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $dbObj = new Database($this->getConnectionObject($mock));
        $this->expectException(DatabaseException::class);
        $dbObj->getCollection('my_new_collection');
    }

    public function testConstructorThrowDatabaseException()
    {
        // Mock error
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(DatabaseException::class);
        $dbObj = new Database($this->getConnectionObject($mock));
    }
}
