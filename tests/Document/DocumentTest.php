<?php


namespace Unit\Document;

use Unit\TestCase;
use ArangoDB\Document\Document;
use ArangoDB\Collection\Collection;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

class DocumentTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function getAttributes()
    {
        return [
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
//
//    public function testSetterThrowException()
//    {
//        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
//        $this->expectException(\Exception::class);
//        $collection->randomProperty = true;
//    }

}
