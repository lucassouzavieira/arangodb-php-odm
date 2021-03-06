<?php


namespace Unit\Validation\Connection;

use Unit\TestCase;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use ArangoDB\Validation\Connection\ConnectionOptionsValidator;

class ConnectionOptionsValidatorTest extends TestCase
{
    public function testRules()
    {
        $connectionOptions = new ConnectionOptionsValidator([
            'endpoint' => 'http://localhost:8529',
            'database' => '_system',
            'username' => 'someUser',
            'password' => 'somePasswd',
        ]);

        $this->assertIsArray($connectionOptions->rules());
    }

    public function testValidate()
    {
        $connectionOptions = new ConnectionOptionsValidator([
            'endpoint' => 'http://localhost:8529',
            'database' => '_system',
            'username' => 'someUser',
            'password' => 'somePasswd',
        ]);

        $this->assertTrue($connectionOptions->validate());
    }

    public function testConvertsHostAndPortToEndpoint()
    {
        $connectionOptions = new ConnectionOptionsValidator([
            'host' => 'http://localhost',
            'port' => 8529,
            'database' => '_system',
            'username' => 'someUser',
            'password' => 'somePasswd',
        ]);

        $this->assertTrue($connectionOptions->validate());
        $this->assertArrayHasKey('endpoint', $connectionOptions->getConnectionOptions());
    }

    public function testThrowMissingParameterExceptionForRequired()
    {
        $connectionOptions = new ConnectionOptionsValidator([
            'database' => '_system',
            'username' => 'someUser',
            'password' => 'somePasswd',
        ]);

        $this->expectException(MissingParameterException::class);
        $this->assertTrue($connectionOptions->validate());
    }

    public function testThrowInvalidParameterException()
    {
        $connectionOptions = new ConnectionOptionsValidator([
            'endpoint' => 'http://localhost:8529',
            'database' => '_system',
            'username' => 'someUser',
            'password' => 'somePasswd',
            'connection' => 'Open'
        ]);

        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($connectionOptions->validate());
    }
}
