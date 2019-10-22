<?php

namespace Unit\Admin;

use ArangoDB\Admin\Admin;
use Unit\TestCase;
use ArangoDB\Admin\Server;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Exceptions\ServerException;

class AdminTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testStatistics()
    {
        $statistics = Admin::statistics($this->getConnectionObject());
        $this->assertIsArray($statistics);
    }

    public function testStatisticsThrowServerException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        $statistics = Admin::statistics($this->getConnectionObject($mock));
    }
}
