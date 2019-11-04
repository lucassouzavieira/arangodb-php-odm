<?php


namespace Unit\Document;

use ArangoDB\Document\Edge;
use GuzzleHttp\Psr7\Response;
use ArangoDB\Document\Document;
use ArangoDB\Collection\Collection;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Exceptions\DatabaseException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use GuzzleHttp\Handler\MockHandler;

class EdgeTest extends DocumentTestCase
{
    public function getAttributes($withDescriptors = false)
    {
        $fake = parent::getAttributes($withDescriptors);
        $edgeDescriptors = [
            '_to' => 'sd/178534',
            '_from' => 'sd/178538',
        ];

        return array_merge($edgeDescriptors, $fake);
    }

    public function testConstructorThrowMissingParameterExceptionForToAttribute()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = new Collection('doc_tests', $db);
        $attributes = $this->getAttributes(true);
        unset($attributes['_to']);

        $this->expectException(MissingParameterException::class);
        $document = new Edge($attributes);
    }

    public function testConstructorThrowMissingParameterExceptionForFromAttribute()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = new Collection('doc_tests', $db);
        $attributes = $this->getAttributes(true);
        unset($attributes['_from']);

        $this->expectException(MissingParameterException::class);
        $document = new Edge($attributes);
    }

    public function testConstructorThrowInvalidParameterException()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = new Collection('doc_tests', $db);
        $attributes = $this->getAttributes(true);
        $attributes['field'] = new ArrayList();

        $this->expectException(InvalidParameterException::class);
        $document = new Edge($attributes);
    }

    public function testConstructOldDocument()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = new Collection('doc_tests', $db);

        $document = new Edge($this->getAttributes(true), $collection);
        $this->assertFalse($document->isNew());
        $this->assertIsString($document->getId());
        $this->assertIsString($document->getKey());
        $this->assertIsString($document->getRevision());
    }

    public function testTo()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'count' => 0])),
            new Response(200, [], json_encode($this->getAttributes(true)))
        ]);

        $db = $this->getConnectionObject($mock)->getDatabase();
        $collection = $db->createCollection('test_coll', ['type' => 3]);

        $this->assertEquals(0, $collection->count());

        $edge = new Edge($this->getAttributes(true), $collection);
        $toDocument = $edge->to();
        $this->assertInstanceOf(Edge::class, $toDocument);
    }

    public function testFrom()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'count' => 0])),
            new Response(200, [], json_encode($this->getAttributes(true)))
        ]);

        $db = $this->getConnectionObject($mock)->getDatabase();
        $collection = $db->createCollection('test_coll', ['type' => 3]);

        $this->assertEquals(0, $collection->count());

        $edge = new Edge($this->getAttributes(true), $collection);
        $fromDocument = $edge->from();
        $this->assertInstanceOf(Edge::class, $fromDocument);
    }

    public function testToReturnFalse()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'count' => 0])),
            new Response(404, [], json_encode($this->getAttributes(true)))
        ]);

        $db = $this->getConnectionObject($mock)->getDatabase();
        $collection = $db->createCollection('test_coll', ['type' => 3]);

        $this->assertEquals(0, $collection->count());

        $edge = new Edge($this->getAttributes(true), $collection);
        $toDocument = $edge->to();
        $this->assertFalse($toDocument);
    }

    public function testFromReturnFalse()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'count' => 0])),
            new Response(404, [], json_encode($this->getAttributes(true)))
        ]);

        $db = $this->getConnectionObject($mock)->getDatabase();
        $collection = $db->createCollection('test_coll', ['type' => 3]);

        $this->assertEquals(0, $collection->count());

        $edge = new Edge($this->getAttributes(true), $collection);
        $fromDocument = $edge->from();
        $this->assertFalse($fromDocument);
    }
}
