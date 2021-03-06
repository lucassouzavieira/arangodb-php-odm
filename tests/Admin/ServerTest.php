<?php

namespace Unit\Admin;

use Unit\TestCase;
use ArangoDB\Admin\Server;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Exceptions\ServerException;

class ServerTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testVersion()
    {
        $version = Server::version($this->getConnectionObject());
        $this->assertIsString($version);
        $this->assertEquals($_ENV['ARANGODB_VERSION'], $version);
    }

    public function testVersionThrowServerException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        Server::version($this->getConnectionObject($mock));
    }

    public function testIsAvailable()
    {
        $status = Server::isAvailable($this->getConnectionObject());
        $this->assertTrue($status);

        $mock = new MockHandler([
            new Response(503, [], json_encode([]))
        ]);

        $status = Server::isAvailable($this->getConnectionObject($mock));
        $this->assertFalse($status);
    }

    public function testIsAvailableThrowServerException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        Server::isAvailable($this->getConnectionObject($mock));
    }

    public function testEngine()
    {
        $engine = Server::engine($this->getConnectionObject());
        $this->assertIsString($engine);
        $this->assertTrue(in_array($engine, ['mmfiles', 'rocksdb']));
    }

    public function testEngineThrowServerException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        Server::engine($this->getConnectionObject($mock));
    }

    public function testRole()
    {
        $role = Server::role($this->getConnectionObject());
        $this->assertIsString($role);
        $this->assertTrue(in_array($role, ['single', 'coordinator', 'primary', 'secondary', 'agent', 'undefined']));
    }

    public function testRoleThrowServerException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        Server::role($this->getConnectionObject($mock));
    }

    public function testLogLevel()
    {
        $logConfiguration = Server::logLevel($this->getConnectionObject());
        $this->assertIsArray($logConfiguration);
    }

    public function testLogLevelThrowServerException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        Server::logLevel($this->getConnectionObject($mock));
    }
}
