<?php
declare(strict_types=1);

namespace Unit\Database;

use Unit\TestCase;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Connection\Connection;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Database\DatabaseHandler;
use ArangoDB\Exceptions\Database\DatabaseException;

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

    public function testCurrentThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(DatabaseException::class);
        $list = DatabaseHandler::current($this->getConnectionObject($mock));
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

    public function testUserDatabasesThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(DatabaseException::class);
        $list = DatabaseHandler::userDatabases($this->getConnectionObject($mock));
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
        $this->assertTrue(in_array('tdbb', $list->toArray()));

        $this->assertTrue(DatabaseHandler::drop($conn, 'tdba'));
        $this->assertTrue(DatabaseHandler::drop($conn, 'tdbb'));
    }

    public function testListThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(DatabaseException::class);
        $list = DatabaseHandler::list($this->getConnectionObject($mock));
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

    public function testDropThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(DatabaseException::class);
        $list = DatabaseHandler::drop($this->getConnectionObject($mock), 'somedb');
    }
}
