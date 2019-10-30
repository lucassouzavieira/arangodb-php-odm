<?php


namespace Unit\Collection\GeneralIndex;

use Unit\TestCase;
use ArangoDB\Collection\Collection;
use ArangoDB\Collection\Index\Index;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

class IndexTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->getConnectionObject()->getDatabase()->dropCollection('index_coll');
        parent::tearDown();
    }

    public function testConstructor()
    {
        $index = new Index("geo", ['location']);

        $this->assertFalse($index->isSparse());
        $this->assertFalse($index->isUnique());
        $this->assertTrue($index->isNew());

        $this->assertEquals("", $index->getId());
        $this->assertEquals("", $index->getName());
        $this->assertEquals("geo", $index->getType());
    }

    public function testConstructorThrowInvalidParameterExceptionForInvalidType()
    {
        $this->expectException(InvalidParameterException::class);
        $index = new Index("easy", ['location']);
    }

    public function testConstructorThrowInvalidParameterExceptionForInvalidKey()
    {
        $this->expectException(InvalidParameterException::class);
        $index = new Index("fulltext", [154]);
    }

    public function testToString()
    {
        $index = new Index("skiplist", ['custom_field']);
        $this->assertIsString((string)$index);
    }

    public function testGetAndSetCollection()
    {
        $collection = $this->getConnectionObject()->getDatabase()->createCollection('index_coll');
        $index = new Index("skiplist", ['custom_field']);
        $this->assertNull($index->getCollection());

        $index->setCollection($collection);
        $this->assertInstanceOf(Collection::class, $index->getCollection());
    }

    public function testJsonSerialize()
    {
        $collection = $this->getConnectionObject()->getDatabase()->createCollection('index_coll');
        $index = $collection->getIndexes()->first();
        $this->assertJson(json_encode($index));
    }

    public function testIsNew()
    {
        $index = new Index("skiplist", ['custom_field']);
        $this->assertTrue($index->isNew());

        // Already existent index
        $collection = $this->getConnectionObject()->getDatabase()->createCollection('index_coll');
        $index = $collection->getIndexes()->first();
        $this->assertFalse($index->isNew());
    }

    public function testGetFields()
    {
        $index = new Index("skiplist", ['custom_field']);
        $this->assertIsArray($index->getFields());

        // Already existent index
        $collection = $this->getConnectionObject()->getDatabase()->createCollection('index_coll');
        $index = $collection->getIndexes()->first();
        $this->assertIsArray($index->getFields());
        $this->assertEquals("_key", $index->getFields()[0]);
    }

    public function testGetCreateData()
    {
        $index = new Index("skiplist", ['custom_field']);
        $this->assertIsArray($index->getCreateData());
        $this->assertArrayHasKey("type", $index->getCreateData());
    }
}
