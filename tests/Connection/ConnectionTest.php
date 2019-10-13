<?php

namespace Unit\Connection;

use Dotenv\Dotenv;
use Unit\TestCase;
use ArangoDB\Connection\Connection;

class ConnectionTest extends TestCase
{
    protected $env;

    public function setUp(): void
    {
        $this->env = Dotenv::create(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../');
        $this->env->load();

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
