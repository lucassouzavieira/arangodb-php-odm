<?php


namespace Unit\Database;

use ArangoDB\Collection\Collection;
use Unit\TestCase;
use ArangoDB\Database\Database;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Database\DatabaseHandler;
use ArangoDB\Exceptions\DatabaseException;

class DatabaseTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testGetDatabaseName()
    {
        $db = new Database($this->getConnectionObject());
        $this->assertEquals(getenv('ARANGODB_DBNAME'), $db->getDatabaseName());
    }

    public function testGetInfo()
    {
        $db = new Database($this->getConnectionObject());
        $info = $db->getInfo();
        $this->assertIsArray($db->getInfo());
        $this->assertEquals($info['name'], getenv('ARANGODB_DBNAME'));
    }

    public function testCreateCollection()
    {
        $db = new Database($this->getConnectionObject());
        $collection = $db->createCollection('my_new_collection');
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertNotEmpty($collection->getGloballyUniqueId());
    }
}
