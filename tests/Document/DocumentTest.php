<?php


namespace Unit\Document;

use Unit\TestCase;
use GuzzleHttp\Psr7\Response;
use ArangoDB\Document\Document;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Collection\Collection;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Exceptions\DatabaseException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

class DocumentTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function getAttributes($withDescriptors = false)
    {
        $descriptors = [
            '_id' => 'sd/178538',
            '_rev' => '_ZcQ9yh----',
            '_key' => '178538'
        ];

        $fake = [
            'field' => 'of soccer',
            'good_music' => [
                'Queen',
                'Motorhead',
                'Anthrax',
                'Metallica',
            ],
            'status' => false,
            'dreamers' => null,
            'value' => 1.5,
            'percent' => 45.4,
            'quantity' => 40
        ];

        if ($withDescriptors) {
            return array_merge($descriptors, $fake);
        }

        return $fake;
    }

    public function testConstructorThrowInvalidParameterException()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = new Collection('doc_tests', $db);

        $this->expectException(InvalidParameterException::class);
        $document = new Document($collection, ['field' => new ArrayList()]);
    }

    public function testToArray()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = new Collection('doc_tests', $db);

        $document = new Document($collection, $this->getAttributes());
        $docArray = $document->toArray();

        $this->assertIsArray($docArray);
        $this->assertArrayHasKey('_id', $docArray);
        $this->assertArrayHasKey('_key', $docArray);
        $this->assertArrayHasKey('_rev', $docArray);
        $this->assertArrayHasKey('status', $docArray);
        $this->assertFalse($docArray['status']);
    }

    public function testConstructOldDocument()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = new Collection('doc_tests', $db);

        $document = new Document($collection, $this->getAttributes(true));
        $this->assertFalse($document->isNew());
        $this->assertIsString($document->getId());
        $this->assertIsString($document->getKey());
        $this->assertIsString($document->getRevision());
    }

    public function testJsonSerialize()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($collection, $this->getAttributes());

        $this->assertJson(json_encode($document));
    }

    public function testGetter()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($collection, $this->getAttributes());

        $this->assertEquals('Queen', $document->good_music[0]);
        $this->assertFalse($document->status);
        $this->assertIsArray($document->good_music);

        $this->assertNull($document->randomProperty);
    }

    public function testSetter()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($collection, $this->getAttributes());

        $this->assertEquals($this->getAttributes()['good_music'], $document->good_music);

        $document->status = true;
        $document->good_music = ['Madonna', 'Beyoncé', 'Kylie Minogue'];

        $this->assertTrue($document->status);
        $this->assertNotEquals($this->getAttributes()['good_music'], $document->good_music);
        $this->assertIsArray($document->good_music);
    }

    public function testSetterThrowException()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($collection, $this->getAttributes());

        $this->expectException(InvalidParameterException::class);
        $document->doc_list = new ArrayList([]);

        $this->assertEquals($this->getAttributes()['good_music'], $document->good_music);
    }

    public function testIsset()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($collection, $this->getAttributes());

        $this->assertTrue(isset($document->field));
        $this->assertFalse(isset($document->randomProp));
    }

    public function testUnset()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($collection, $this->getAttributes());

        $this->assertTrue(isset($document->field));

        unset($document->field);

        $this->assertFalse(isset($document->field));
    }

    public function testToString()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($collection, $this->getAttributes());
        $this->assertIsString((string)$document);
    }

    public function testIsNew()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $document = new Document($collection, $this->getAttributes());

        // Check 'isNew' returns
        $this->assertTrue($document->isNew());

        // After creations, 'isNew' must be false.
        // $this->assertFalse($document->isNew());
        // TODO add the test after save method
    }

    public function testSave()
    {
        $db = $this->getConnectionObject()->getDatabase();
        $collection = $db->createCollection('test_coll');

        $this->assertEquals(0, $collection->count());

        $document = new Document($collection, $this->getAttributes());
        $this->assertTrue($document->save());

        // We cannot save the document twice. Must return false on second call.
        $this->assertFalse($document->save());

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

        $document = new Document($collection, $this->getAttributes());
        $this->expectException(DatabaseException::class);
        $this->assertTrue($document->save());
    }

//    public function testUpdate()
//    {
//        $db = $this->getConnectionObject()->getDatabase();
//        $collection = $db->createCollection('test_coll');
//
//        $this->assertEquals(0, $collection->count());
//
//        $document = new Document($collection, $this->getAttributes());
//        $this->assertTrue($document->save());
//
//        $this->assertEquals(1, $collection->count());
//        unset($document->field);
//        $document->new_attr = true;
//
//        $this->assertTrue($document->update());
//        $this->assertEquals(1, $collection->count());
//
//        $updated = $collection->all()->first();
//        $this->assertEquals(true, $updated->new_attr);
//        $this->assertNull($updated->field);
//
//        $collection->drop();
//    }
}
