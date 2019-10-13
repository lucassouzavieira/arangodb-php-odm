<?php


namespace Unit\Auth;

use Unit\TestCase;
use ArangoDB\Auth\User;
use ArangoDB\Connection\Connection;

class UserTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testAll()
    {
        $connection = new Connection([
            'username' => getenv('ARANGODB_USERNAME'),
            'password' => getenv('ARANGODB_PASSWORD'),
            'database' => getenv('ARANGODB_DBNAME'),
            'host' => getenv('ARANGODB_HOST'),
            'port' => getenv('ARANGODB_PORT')
        ]);

        $user = new User();
        $user->setConnection($connection);
        $data = $user->all();
        $this->assertIsArray($data);
        $this->assertCount(2, $data);
    }
}
