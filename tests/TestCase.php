<?php

namespace Unit;

use Dotenv\Dotenv;
use GuzzleHttp\HandlerStack;
use ArangoDB\Http\RestClient;
use ArangoDB\Connection\Connection;
use GuzzleHttp\Handler\MockHandler;
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
        $this->env = Dotenv::createImmutable(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../');
        $this->env->load();
    }

    /**
     * @param MockHandler|null $mock
     * @return Connection
     * @throws \ArangoDB\Auth\Exceptions\AuthException
     * @throws \ArangoDB\Exceptions\ConnectionException
     * @throws \ArangoDB\Validation\Exceptions\InvalidParameterException
     * @throws \ArangoDB\Validation\Exceptions\MissingParameterException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     */
    public function getConnectionObject(MockHandler $mock = null)
    {
        $connection = new Connection([
            'username' => $_ENV['ARANGODB_USERNAME'],
            'password' => $_ENV['ARANGODB_PASSWORD'],
            'database' => $_ENV['ARANGODB_DBNAME'],
            'host' => $_ENV['ARANGODB_HOST'],
            'port' => $_ENV['ARANGODB_PORT']
        ]);

        if ($mock) {
            $handler = HandlerStack::create($mock);
            $restClient = new RestClient($connection->getBaseUri(), ['handler' => $handler]);

            // Set 'restClient' into Connection
            $reflection = new \ReflectionClass($connection);
            $reflectionProperty = $reflection->getProperty('restClient');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($connection, $restClient);
            $reflectionProperty->setAccessible(false);
        }

        return $connection;
    }

    public function mockServerError()
    {
        return [
            'error' => true,
            'errorMessage' => 'Mocked error',
            'errorNum' => 0,
            'code' => 0
        ];
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
