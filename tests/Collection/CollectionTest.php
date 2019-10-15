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

        $this->assertNull($collection->randomProperty);
    }

    public function testSetter()
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('any', $collection->name);
        $collection->waitForSync = true;
        $collection->doCompact = false;
        $collection->name = 'newAny';

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
}
