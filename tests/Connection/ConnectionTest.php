<?php

namespace Unit\Connection;

use ArangoDB\Database\Database;
use Unit\TestCase;
use ArangoDB\Connection\Connection;
use ArangoDB\Exceptions\ConnectionException;
use ArangoDB\Auth\Exceptions\AuthException;

class ConnectionTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testAuthenticate()
    {
        $connection = new Connection([
            'username' => getenv('ARANGODB_USERNAME'),
            'password' => getenv('ARANGODB_PASSWORD'),
            'database' => getenv('ARANGODB_DBNAME'),
            'host' => getenv('ARANGODB_HOST'),
            'port' => getenv('ARANGODB_PORT')
        ]);

        $this->assertNotNull($connection);
        $this->assertTrue($connection->isAuthenticated());
    }

    public function testThrowAuthException()
    {
        $this->expectException(AuthException::class);

        $connection = new Connection([
            'username' => getenv('ARANGODB_USERNAME'),
            'password' => 'someWrongPassword',
            'database' => getenv('ARANGODB_DBNAME'),
            'host' => getenv('ARANGODB_HOST'),
            'port' => getenv('ARANGODB_PORT')
        ]);

        $this->expectException(AuthException::class);

        $connection = new Connection([
            'username' => 'usernamenonexistent',
            'password' => getenv('ARANGODB_PASSWORD'),
            'database' => getenv('ARANGODB_DBNAME'),
            'host' => getenv('ARANGODB_HOST'),
            'port' => getenv('ARANGODB_PORT')
        ]);
    }

    public function testThrowConnectionException()
    {
        $this->expectException(ConnectionException::class);
        $connection = new Connection([
            'username' => getenv('ARANGODB_USERNAME'),
            'password' => getenv('ARANGODB_PASSWORD'),
            'database' => getenv('ARANGODB_DBNAME'),
            'host' => getenv('ARANGODB_HOST'),
            'port' => rand(8100, 8200) // Wrong port
        ]);
    }

    public function testGetDatabaseName()
    {
        $connection = new Connection([
            'username' => getenv('ARANGODB_USERNAME'),
            'password' => getenv('ARANGODB_PASSWORD'),
            'database' => getenv('ARANGODB_DBNAME'),
            'host' => getenv('ARANGODB_HOST'),
            'port' => getenv('ARANGODB_PORT')
        ]);

        $this->assertNotNull($connection);
        $this->assertEquals(getenv('ARANGODB_DBNAME'), $connection->getDatabaseName());
    }

    public function testGetUsername()
    {
        $connection = new Connection([
            'username' => getenv('ARANGODB_USERNAME'),
            'password' => getenv('ARANGODB_PASSWORD'),
            'database' => getenv('ARANGODB_DBNAME'),
            'host' => getenv('ARANGODB_HOST'),
            'port' => getenv('ARANGODB_PORT')
        ]);

        $this->assertNotNull($connection);
        $this->assertEquals(getenv('ARANGODB_USERNAME'), $connection->getUsername());
    }

    public function testGetDatabase()
    {
        $connection = new Connection([
            'username' => getenv('ARANGODB_USERNAME'),
            'password' => getenv('ARANGODB_PASSWORD'),
            'database' => getenv('ARANGODB_DBNAME'),
            'host' => getenv('ARANGODB_HOST'),
            'port' => getenv('ARANGODB_PORT')
        ]);

        $this->assertNotNull($connection);
        $this->assertInstanceOf(Database::class, $connection->getDatabase());
        $this->assertEquals(getenv('ARANGODB_DBNAME'), $connection->getDatabase()->getDatabaseName());
        $this->assertEquals(getenv('ARANGODB_DBNAME'), $connection->getDatabaseName());
    }

    public function testGetBaseUri()
    {
        $connection = new Connection([
            'username' => getenv('ARANGODB_USERNAME'),
            'password' => getenv('ARANGODB_PASSWORD'),
            'database' => getenv('ARANGODB_DBNAME'),
            'host' => getenv('ARANGODB_HOST'),
            'port' => getenv('ARANGODB_PORT')
        ]);

        $this->assertNotNull($connection);
        $this->assertEquals(sprintf("%s:%d", getenv('ARANGODB_HOST'), getenv('ARANGODB_PORT')), $connection->getBaseUri());
    }

    public function testGetAuthorizationHeader()
    {
        $connection = new Connection([
            'username' => getenv('ARANGODB_USERNAME'),
            'password' => getenv('ARANGODB_PASSWORD'),
            'database' => getenv('ARANGODB_DBNAME'),
            'host' => getenv('ARANGODB_HOST'),
            'port' => getenv('ARANGODB_PORT')
        ]);

        $this->assertNotNull($connection);
        $getAuthorizationHeaders = new \ReflectionMethod(Connection::class, 'getAuthorizationHeader');
        $getAuthorizationHeaders->setAccessible(true);
        $this->assertArrayHasKey('Authorization', $getAuthorizationHeaders->invoke($connection));
    }
}
