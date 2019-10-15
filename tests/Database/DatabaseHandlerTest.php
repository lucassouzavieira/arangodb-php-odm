<?php


namespace Unit\Database;

use ArangoDB\DataStructures\ArrayList;
use Unit\TestCase;
use ArangoDB\Database\DatabaseHandler;
use ArangoDB\Connection\Connection;
use ArangoDB\Exceptions\DatabaseException;

class DatabaseHandlerTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testCreate()
    {
        $conn = $this->getConnectionObject();
        $result = DatabaseHandler::create($conn, 'test_db');
        $this->assertTrue($result);
        $this->assertTrue(DatabaseHandler::drop($conn, 'test_db'));
    }

    public function testCurrent()
    {
        $conn = $this->getConnectionObject();
        $current = DatabaseHandler::current($conn);
        $this->assertIsArray($current);
        $this->assertEquals($current['name'], getenv('ARANGODB_DBNAME'));
    }

    public function testUserDatabases()
    {
        $conn = $this->getConnectionObject();
        $result = DatabaseHandler::create($conn, 'tdba');
        $this->assertTrue($result);
        $result = DatabaseHandler::create($conn, 'tdbb');
        $this->assertTrue($result);
        $list = DatabaseHandler::userDatabases($conn);

        $this->assertInstanceOf(ArrayList::class, $list);
        $this->assertTrue(in_array('tdba', $list->toArray()));
        $this->assertTrue(in_array('tdba', $list->toArray()));
        $this->assertTrue(in_array(getenv('ARANGODB_DBNAME'), $list->toArray()));

        $this->assertTrue(DatabaseHandler::drop($conn, 'tdba'));
        $this->assertTrue(DatabaseHandler::drop($conn, 'tdbb'));
    }

    public function testList()
    {
        $conn = $this->getConnectionObject();
        $result = DatabaseHandler::create($conn, 'tdba');
        $this->assertTrue($result);
        $result = DatabaseHandler::create($conn, 'tdbb');
        $this->assertTrue($result);

        $list = DatabaseHandler::list($conn);
        $this->assertInstanceOf(ArrayList::class, $list);
        $this->assertTrue(in_array('tdba', $list->toArray()));
        $this->assertTrue(in_array('tdba', $list->toArray()));

        $this->assertTrue(DatabaseHandler::drop($conn, 'tdba'));
        $this->assertTrue(DatabaseHandler::drop($conn, 'tdbb'));
    }

    public function testThrowDuplicateDatabaseException()
    {
        $conn = $this->getConnectionObject();
        $this->expectException(DatabaseException::class);
        $result = DatabaseHandler::create($conn, getenv('ARANGODB_DBNAME'));
        $this->assertTrue($result);
    }

    public function testDrop()
    {
        $conn = $this->getConnectionObject();
        $result = DatabaseHandler::create($conn, 'test_db');
        $this->assertTrue($result);

        $dropResult = DatabaseHandler::drop($conn, 'test_db');
        $this->assertTrue($dropResult);
    }

    public function testDropNonExistentDatabaseMustReturnFalse()
    {
        $conn = $this->getConnectionObject();
        $dropResult = DatabaseHandler::drop($conn, 'test_db');
        $this->assertFalse($dropResult);
    }
}
