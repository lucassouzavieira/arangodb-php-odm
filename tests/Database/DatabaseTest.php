<?php


namespace Unit\Database;

use ArangoDB\DataStructures\ArrayList;
use Unit\TestCase;
use ArangoDB\Database\Database;
use ArangoDB\Connection\Connection;
use ArangoDB\Exceptions\DatabaseException;

class DatabaseTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testCreate()
    {
        $conn = $this->getConnectionObject();
        $result = Database::create($conn, 'test_db');
        $this->assertTrue($result);
        $this->assertTrue(Database::drop($conn, 'test_db'));
    }

    public function testCurrent()
    {
        $conn = $this->getConnectionObject();
        $current = Database::current($conn);
        $this->assertIsArray($current);
        $this->assertEquals($current['name'], getenv('ARANGODB_DBNAME'));
    }

    public function testUserDatabases()
    {
        $conn = $this->getConnectionObject();
        $result = Database::create($conn, 'tdba');
        $this->assertTrue($result);
        $result = Database::create($conn, 'tdbb');
        $this->assertTrue($result);
        $list = Database::userDatabases($conn);

        $this->assertInstanceOf(ArrayList::class, $list);
        $this->assertTrue(in_array('tdba', $list->toArray()));
        $this->assertTrue(in_array('tdba', $list->toArray()));
        $this->assertTrue(in_array(getenv('ARANGODB_DBNAME'), $list->toArray()));

        $this->assertTrue(Database::drop($conn, 'tdba'));
        $this->assertTrue(Database::drop($conn, 'tdbb'));
    }

    public function testList()
    {
        $conn = $this->getConnectionObject();
        $result = Database::create($conn, 'tdba');
        $this->assertTrue($result);
        $result = Database::create($conn, 'tdbb');
        $this->assertTrue($result);

        $list = Database::list($conn);
        $this->assertInstanceOf(ArrayList::class, $list);
        $this->assertTrue(in_array('tdba', $list->toArray()));
        $this->assertTrue(in_array('tdba', $list->toArray()));

        $this->assertTrue(Database::drop($conn, 'tdba'));
        $this->assertTrue(Database::drop($conn, 'tdbb'));
    }

    public function testThrowDuplicateDatabaseException()
    {
        $conn = $this->getConnectionObject();
        $this->expectException(DatabaseException::class);
        $result = Database::create($conn, getenv('ARANGODB_DBNAME'));
        $this->assertTrue($result);
    }

    public function testDrop()
    {
        $conn = $this->getConnectionObject();
        $result = Database::create($conn, 'test_db');
        $this->assertTrue($result);

        $dropResult = Database::drop($conn, 'test_db');
        $this->assertTrue($dropResult);
    }

    public function testDropNonExistentDatabaseMustReturnFalse()
    {
        $conn = $this->getConnectionObject();
        $dropResult = Database::drop($conn, 'test_db');
        $this->assertFalse($dropResult);
    }
}
