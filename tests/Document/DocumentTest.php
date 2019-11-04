<?php


namespace Unit\Document;

use GuzzleHttp\Psr7\Response;
use ArangoDB\Document\Document;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Collection\Collection;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Exceptions\DatabaseException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

class DocumentTest extends DocumentTestCase
{
    public function testConstructorThrowInvalidParameterException()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = new Collection('doc_tests', $db);

        $this->expectException(InvalidParameterException::class);
        $document = new Document(['field' => new ArrayList()]);
    }

    public function testConstructOldDocument()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = new Collection('doc_tests', $db);

        $document = new Document($this->getAttributes(true), $collection);
        $this->assertFalse($document->isNew());
        $this->assertIsString($document->getId());
        $this->assertIsString($document->getKey());
        $this->assertIsString($document->getRevision());
    }

    public function testGetCollectionAndSetCollection()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = new Collection('doc_tests', $db);

        $document = new Document($this->getAttributes());
        $this->assertNull($document->getCollection());

        $document->setCollection($collection);

        $this->assertInstanceOf(Collection::class, $document->getCollection());
    }

    public function testToArray()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = new Collection('doc_tests', $db);

        $document = new Document($this->getAttributes(), $collection);
        $docArray = $document->toArray();

        $this->assertIsArray($docArray);
        $this->assertArrayHasKey('_id', $docArray);
        $this->assertArrayHasKey('_key', $docArray);
        $this->assertArrayHasKey('_rev', $docArray);
        $this->assertArrayHasKey('status', $docArray);
        $this->assertFalse($docArray['status']);
    }

    public function testJsonSerialize()
    {
        $collection = new Collection('document_test_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($this->getAttributes(), $collection);

        $this->assertJson(json_encode($document));
    }

    public function testGetter()
    {
        $collection = new Collection('document_test_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($this->getAttributes(), $collection);

        $this->assertEquals('Queen', $document->good_music[0]);
        $this->assertFalse($document->status);
        $this->assertIsArray($document->good_music);

        $this->assertNull($document->randomProperty);
    }

    public function testSetter()
    {
        $collection = new Collection('document_test_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($this->getAttributes(), $collection);

        $this->assertEquals($this->getAttributes()['good_music'], $document->good_music);

        $document->status = true;
        $document->good_music = ['Madonna', 'Beyoncé', 'Kylie Minogue'];

        $this->assertTrue($document->status);
        $this->assertNotEquals($this->getAttributes()['good_music'], $document->good_music);
        $this->assertIsArray($document->good_music);
    }

    public function testSetterThrowException()
    {
        $collection = new Collection('document_test_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($this->getAttributes(), $collection);

        $this->expectException(InvalidParameterException::class);
        $document->doc_list = new ArrayList([]);

        $this->assertEquals($this->getAttributes()['good_music'], $document->good_music);
    }

    public function testIsset()
    {
        $collection = new Collection('document_test_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($this->getAttributes(), $collection);

        $this->assertTrue(isset($document->field));
        $this->assertFalse(isset($document->randomProp));
    }

    public function testUnset()
    {
        $collection = new Collection('document_test_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($this->getAttributes(), $collection);

        $this->assertTrue(isset($document->field));

        unset($document->field);

        $this->assertFalse(isset($document->field));
    }

    public function testToString()
    {
        $collection = new Collection('document_test_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($this->getAttributes(), $collection);
        $this->assertIsString((string)$document);
    }

    public function testIsNew()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = $db->createCollection('document_test_coll');
        $document = new Document($this->getAttributes(), $collection);

        // Check 'isNew' returns
        $this->assertTrue($document->isNew());

        $this->assertTrue($document->save());

        // After creations, 'isNew' must be false.
        $this->assertFalse($document->isNew());
        $collection->drop();
    }

    public function testSave()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = $db->createCollection('test_coll');

        $this->assertEquals(0, $collection->count());

        $document = new Document($this->getAttributes(), $collection);
        $this->assertTrue($document->save());

        $this->assertEquals(1, $collection->count());

        $collection->drop();
    }

    public function testSaveThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'count' => 0])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = $this->getConnectionObject($mock)->getDatabase();
        $collection = $db->createCollection('test_coll');

        $this->assertEquals(0, $collection->count());

        $document = new Document($this->getAttributes(), $collection);
        $this->expectException(DatabaseException::class);
        $this->assertTrue($document->save());
    }

    public function testUpdate()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = $db->createCollection('test_coll');

        $this->assertEquals(0, $collection->count());

        $document = new Document($this->getAttributes(), $collection);
        $this->assertTrue($document->save());

        $this->assertEquals(1, $collection->count());
        unset($document->field);
        $document->new_attr = true;

        $this->assertTrue($document->save());
        $this->assertEquals(1, $collection->count());

        $updated = $collection->all()->current()->toArray();
        $updated = new Document($updated);
        $this->assertEquals(true, $updated->new_attr);
        $this->assertNull($updated->field);

        $collection->drop();
    }

    public function testUpdateThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'count' => 0])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = $this->getConnectionObject($mock)->getDatabase();
        $collection = $db->createCollection('test_coll');

        $this->assertEquals(0, $collection->count());

        $document = new Document($this->getAttributes(), $collection);
        $this->expectException(DatabaseException::class);
        $document->save();
    }

    public function testUpdateThrowDatabaseExceptionOnConnectionFail()
    {
        $descriptors = [
            '_id' => 'test_coll/178538',
            '_rev' => '_ZcQ9yh----',
            '_key' => '178538'
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'count' => 0])),
            new Response(200, [], json_encode($descriptors)),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = $this->getConnectionObject($mock)->getDatabase();
        $collection = $db->createCollection('test_coll');

        $this->assertEquals(0, $collection->count());

        $document = new Document($this->getAttributes(), $collection);
        $this->assertTrue($document->save());

        $this->expectException(DatabaseException::class);
        $document->save();
    }

    public function testDelete()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = $db->createCollection('test_coll');

        $this->assertEquals(0, $collection->count());

        $document = new Document($this->getAttributes(), $collection);
        $this->assertTrue($document->save());

        $this->assertEquals(1, $collection->count());

        $this->assertTrue($document->delete());
        $this->assertEquals(0, $collection->count());

        $collection->drop();
    }

    public function testDeleteForNewDocuments()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = $db->createCollection('test_coll');

        $this->assertEquals(0, $collection->count());
        $document = new Document($this->getAttributes(), $collection);
        $this->assertEquals(0, $collection->count());

        $this->assertFalse($document->delete());
        $collection->drop();
    }

    public function testDeleteThrowDatabaseException()
    {
        $descriptors = [
            '_id' => 'test_coll/178538',
            '_rev' => '_ZcQ9yh----',
            '_key' => '178538'
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'count' => 0])),
            new Response(200, [], json_encode($descriptors)),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = $this->getConnectionObject($mock)->getDatabase();
        $collection = $db->createCollection('test_coll');

        $this->assertEquals(0, $collection->count());

        $document = new Document($this->getAttributes(), $collection);
        $this->assertTrue($document->save());

        $this->expectException(DatabaseException::class);
        $document->delete();
    }
}
