<?php


namespace Unit\Cursor;

use GuzzleHttp\Psr7\Response;
use ArangoDB\Document\Document;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Cursor\CollectionCursor;
use ArangoDB\Cursor\Exceptions\CursorException;

class CollectionCursorTest extends CursorTestCase
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
        $cursor = new CollectionCursor($collection);
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

        $cursor = new CollectionCursor($collection);
        $counter = 0;

        // Iterate over cursor
        foreach ($cursor as $value) {
            $counter++;
        }

        $this->assertEquals((2500 - 1), $counter); // Counter starts at 0.
    }

    public function testCurrent()
    {
        $this->getConnectionObject()->getDatabase()->createCollection('test_cursor_coll');
        $doc = new Document(['hello' => 'Sun'], $this->getConnectionObject()->getDatabase()->getCollection('test_cursor_coll'));
        $doc->save();

        $collection = $this->getCollection();
        $cursor = new CollectionCursor($collection);
        $current = $cursor->current();

        $this->assertInstanceOf(Document::class, $current);
        $this->assertFalse($current->isNew());
        $this->assertEquals('Sun', $current->toArray()['hello']);
    }
}
