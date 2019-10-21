<?php


namespace Cursor;

use Unit\TestCase;
use ArangoDB\AQL\Statement;
use ArangoDB\Cursor\Cursor;
use GuzzleHttp\Psr7\Response;
use ArangoDB\Document\Document;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Cursor\Exceptions\CursorException;

class CursorTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->getConnectionObject()->getDatabase()->dropCollection('test_cursor_coll');
        parent::tearDown();
    }

    public function getCollection($quantity = 500)
    {
        $db = $this->getConnectionObject()->getDatabase();

        if (!$db->hasCollection('test_cursor_coll')) {
            $db->createCollection('test_cursor_coll');
        }

        $collection = $db->getCollection('test_cursor_coll');
        $planets = ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupyter', 'Saturn', 'Uranus', 'Neptune'];

        // Create 1000 documents
        for ($i = 0; $i < $quantity; $i++) {
            $document = new Document(['hello' => $planets[rand(0, 7)]], $collection);
            $document->save();
        }

        return $collection;
    }

    public function testConstructorThrowCursorException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'name' => 'test_cursor_coll'])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $connection = $this->getConnectionObject($mock)->getDatabase()->createCollection('test_cursor_coll');
        $collection = $connection->getDatabase()->getCollection('test_cursor_coll');

        $this->expectException(CursorException::class);
        $cursor = $collection->all();
    }

    public function testFetch()
    {
        $collection = $this->getCollection(2500);
        $cursor = $collection->all();
        $counter = 0;

        // Iterate over cursor and force a 'fetch' call
        foreach ($cursor as $value) {
            $counter++;
        }

        $this->assertEquals(2500, $counter);
    }

    public function testFetchingThrowCursorException()
    {
        $collection = $this->getCollection(10);
        $cursor = $collection->all();

        $this->expectException(CursorException::class);
        $cursor->fetch();
    }

    public function testFetchingThrowCursorExceptionOnConnectionFail()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'name' => 'test_cursor_coll'])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'id' => '154875', 'hasMore' => true])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $connection = $this->getConnectionObject($mock)->getDatabase()->createCollection('test_cursor_coll');
        $collection = $connection->getDatabase()->getCollection('test_cursor_coll');

        $cursor = $collection->all();
        $this->expectException(CursorException::class);
        $cursor->fetch();
    }

    public function testToString()
    {
        $collection = $this->getCollection(10);
        $cursor = $collection->all();
        $this->assertIsString((string)$cursor);
    }

    public function testGetId()
    {
        // Small sets of data haven't an Id.
        $collection = $this->getCollection(1250);

        $statement = new Statement("FOR u IN @collection RETURN u");
        $statement->bindValue('@collection', $collection->getName());
        $cursor = new Cursor($this->getConnectionObject(), $statement);

        $this->assertIsString($cursor->getId());
    }

    public function testDelete()
    {
        // Small sets of data haven't an Id.
        $collection = $this->getCollection(1250);

        $cursor = $collection->all();
        $this->assertIsString($cursor->getId());
        $this->assertTrue($cursor->delete());
    }

    public function testDeleteReturnFalse()
    {
        // Small sets of data doesn't have an Id.
        // So the delete method will return false.
        $collection = $this->getCollection(5);

        $cursor = $collection->all();
        $this->assertFalse($cursor->delete());
    }

    public function testDeleteReturnThrowCursorException()
    {
        // Small sets of data doesn't have an Id.
        // So the delete method will return false.
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'name' => 'test_cursor_coll'])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'id' => '154875', 'hasMore' => true])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $connection = $this->getConnectionObject($mock)->getDatabase()->createCollection('test_cursor_coll');
        $collection = $connection->getDatabase()->getCollection('test_cursor_coll');

        $cursor = $collection->all();
        $this->expectException(CursorException::class);
        $this->assertFalse($cursor->delete());
    }

    public function testIterable()
    {
        $collection = $this->getCollection(10);
        $cursor = $collection->all();
        $this->assertIsIterable($cursor);
    }

    public function testCurrent()
    {
        $this->getConnectionObject()->getDatabase()->createCollection('test_cursor_coll');
        $doc = new Document(['hello' => 'Sun'], $this->getConnectionObject()->getDatabase()->getCollection('test_cursor_coll'));
        $doc->save();

        $collection = $this->getCollection();
        $current = $collection->all()->current();

        $this->assertIsArray($current);
        $this->assertEquals('Sun', $current['hello']);
    }

    public function testNext()
    {
        $this->getConnectionObject()->getDatabase()->createCollection('test_cursor_coll');

        // Save 2 documents
        $doc = new Document(['hello' => 'Sun'], $this->getConnectionObject()->getDatabase()->getCollection('test_cursor_coll'));
        $doc->save();
        $doc = new Document(['hello' => 'Proxima Centauri'], $this->getConnectionObject()->getDatabase()->getCollection('test_cursor_coll'));
        $doc->save();

        $collection = $this->getCollection();
        $cursor = $collection->all();
        $this->assertEquals(0, $cursor->key());

        $cursor->next();

        $this->assertEquals(1, $cursor->key());
        $this->assertIsArray($cursor->current());
        $this->assertEquals('Proxima Centauri', $cursor->current()['hello']);
    }

    public function testKey()
    {
        $collection = $this->getCollection(10);
        $cursor = $collection->all();
        $this->assertIsIterable($cursor);

        $cursor->next();
        $cursor->next();

        $this->assertEquals(2, $cursor->key());
    }

    public function testValidIfKeyIsValid()
    {
        $collection = $this->getCollection(5);
        $cursor = $collection->all();
        $this->assertIsIterable($cursor);

        $cursor->next();
        $cursor->next();

        $this->assertEquals(true, $cursor->valid());
    }

    public function testValidIfKeyIsInvalid()
    {
        $collection = $this->getCollection(5);
        $cursor = $collection->all();
        $this->assertIsIterable($cursor);

        $cursor->next();
        $cursor->next();
        $cursor->next();
        $cursor->next();

        $this->assertEquals(false, $cursor->valid());
    }

    public function testRewind()
    {
        $collection = $this->getCollection(5);
        $cursor = $collection->all();
        $this->assertIsIterable($cursor);

        $cursor->next();
        $cursor->next();
        $cursor->next();
        $this->assertEquals(3, $cursor->key());

        $cursor->rewind();
        $this->assertEquals(0, $cursor->key());
    }
}
