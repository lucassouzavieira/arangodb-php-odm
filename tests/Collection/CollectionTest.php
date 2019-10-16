<?php


namespace Unit\Collection;

use Unit\TestCase;
use ArangoDB\Database\Database;
use ArangoDB\Collection\Collection;

class CollectionTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testConstructor()
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertObjectHasAttribute('connection', $collection);
    }

    public function testGetDatabase()
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->assertInstanceOf(Database::class, $collection->getDatabase());
    }

    public function testGetter()
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('any', $collection->name);

        $this->assertFalse($collection->waitForSync);
        $this->assertTrue($collection->doCompact);
        $this->assertNull($collection->id);

        $this->assertNull($collection->randomProperty);
    }

    public function testSetter()
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('any', $collection->name);
        $collection->waitForSync = true;
        $collection->doCompact = false;
        $collection->name = 'newAny';

        $this->assertNull($collection->id);
        $this->assertTrue($collection->waitForSync);
        $this->assertFalse($collection->doCompact);
        $this->assertEquals('newAny', $collection->name);

        $this->assertNull($collection->randomProperty);
    }

    public function testSetterThrowException()
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->expectException(\Exception::class);
        $collection->randomProperty = true;
    }

    public function testGetName()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('we_are_the_champions', $collection->getName());
        $this->assertEquals($collection->name, $collection->getName());
    }

    public function testGetId()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase());
        $this->assertNull($collection->getId());

        $collection->save();
        $this->assertIsString($collection->getId());

        $this->assertTrue($collection->drop());
    }

    public function testGetStatus()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase());
        $this->assertEquals(0, $collection->getStatus());
    }

    public function testGetDescription()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('unknown', $collection->getStatusDescription());
    }

    public function testIsSystem()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase());
        $this->assertFalse($collection->isSystem());

        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $this->assertTrue($collection->isSystem());
    }

    public function testGetAttributes()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $this->assertIsArray($collection->getAttributes());
        $this->assertTrue($collection->getAttributes()['isSystem']);
    }
}
