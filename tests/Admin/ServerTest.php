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
}
