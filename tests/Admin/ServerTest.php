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
        $this->assertEquals(getenv('ARANGODB_VERSION'), $version);
    }

    public function testVersionThrowServerException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        $version = Server::version($this->getConnectionObject($mock));
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
        $status = Server::isAvailable($this->getConnectionObject());
        $this->assertTrue($status);

        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        $status = Server::isAvailable($this->getConnectionObject($mock));
    }
}
