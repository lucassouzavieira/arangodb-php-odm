<?php


namespace Unit\Cursor;

use ArangoDB\AQL\Statement;
use ArangoDB\Cursor\Cursor;
use GuzzleHttp\Psr7\Response;
use ArangoDB\Document\Document;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Cursor\Exceptions\CursorException;

class CursorTest extends CursorTestCase
{
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
        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));
    }

    public function testFetch()
    {
        $defaults = ['extra' => [], 'cached' => false];
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'name' => 'test_cursor_coll'])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(array_merge(['result' => $this->getMockArray(1000), 'id' => '154875', 'hasMore' => true], $defaults))),
            new Response(200, [], json_encode(array_merge(['result' => $this->getMockArray(1000), 'id' => '154875', 'hasMore' => true], $defaults))),
            new Response(200, [], json_encode((array_merge(['result' => $this->getMockArray(500), 'id' => '154875', 'hasMore' => false], $defaults)))),
        ]);

        $connection = $this->getConnectionObject($mock)->getDatabase()->createCollection('test_cursor_coll');
        $collection = $connection->getDatabase()->getCollection('test_cursor_coll');

        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));
        $counter = 0;

        // Iterate over cursor
        foreach ($cursor as $value) {
            $counter++;
        }

        $this->assertEquals((2500 - 1), $counter); // Counter starts at 0.
    }

    public function testFetchingThrowCursorException()
    {
        $collection = $this->getCollection(10);
        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));

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

        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));
        $this->expectException(CursorException::class);
        $cursor->fetch();
    }

    public function testToString()
    {
        $collection = $this->getCollection(10);
        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));
        $this->assertIsString((string)$cursor);
    }

    public function testGetId()
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
        ]);

        $connection = $this->getConnectionObject($mock)->getDatabase()->createCollection('test_cursor_coll');
        $collection = $connection->getDatabase()->getCollection('test_cursor_coll');
        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));

        $this->assertIsString($cursor->getId());
    }

    public function testDelete()
    {
        // Small sets of data haven't an Id.
        $collection = $this->getCollection(1250);

        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));
        $this->assertIsString($cursor->getId());
        $this->assertTrue($cursor->delete());
    }

    public function testDeleteReturnFalse()
    {
        // Small sets of data doesn't have an Id.
        // So the delete method will return false.
        $collection = $this->getCollection(5);

        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));
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

        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));
        $this->expectException(CursorException::class);
        $this->assertFalse($cursor->delete());
    }

    public function testIterable()
    {
        $collection = $this->getCollection(10);
        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));
        $this->assertIsIterable($cursor);
    }

    public function testCurrent()
    {
        $this->getConnectionObject()->getDatabase()->createCollection('test_cursor_coll');
        $doc = new Document(['hello' => 'Sun'], $this->getConnectionObject()->getDatabase()->getCollection('test_cursor_coll'));
        $doc->save();

        $collection = $this->getCollection();
        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));
        $current = $cursor->current();

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
        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));
        $this->assertEquals(0, $cursor->key());

        $cursor->next();

        $this->assertEquals(1, $cursor->key());
        $this->assertIsArray($cursor->current());
        $this->assertEquals('Proxima Centauri', $cursor->current()['hello']);
    }

    public function testKey()
    {
        $collection = $this->getCollection(10);
        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));
        $this->assertIsIterable($cursor);

        $cursor->next();
        $cursor->next();

        $this->assertEquals(2, $cursor->key());
    }

    public function testValidIfKeyIsValid()
    {
        $collection = $this->getCollection(5);
        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));
        $this->assertIsIterable($cursor);

        $cursor->next();
        $cursor->next();

        $this->assertEquals(true, $cursor->valid());
    }

    public function testValidIfKeyIsInvalid()
    {
        $collection = $this->getCollection(5);
        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));
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
        $cursor = new Cursor($collection->getDatabase()->getConnection(), new Statement("FOR u IN test_cursor_coll RETURN u"));
        $this->assertIsIterable($cursor);

        $cursor->next();
        $cursor->next();
        $cursor->next();
        $this->assertEquals(3, $cursor->key());

        $cursor->rewind();
        $this->assertEquals(0, $cursor->key());
    }
}
