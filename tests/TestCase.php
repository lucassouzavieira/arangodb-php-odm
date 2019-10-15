<?php

namespace Unit;

use Dotenv\Dotenv;
use ArangoDB\Connection\Connection;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Abstract base test case class
 *
 * @package Tests
 */
abstract class TestCase extends BaseTestCase
{
    protected $env;

    public function loadEnvironment()
    {
        $this->env = Dotenv::create(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../');
        $this->env->load();
    }

    public function getConnectionObject()
    {
        return new Connection([
            'username' => getenv('ARANGODB_USERNAME'),
            'password' => getenv('ARANGODB_PASSWORD'),
            'database' => getenv('ARANGODB_DBNAME'),
            'host' => getenv('ARANGODB_HOST'),
            'port' => getenv('ARANGODB_PORT')
        ]);
    }


    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
