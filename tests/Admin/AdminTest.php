<?php

namespace Unit\Admin;

use Unit\TestCase;
use ArangoDB\Admin\Admin;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\DataStructures\ArrayList;
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

    public function testTasks()
    {
        $tasks = Admin::tasks($this->getConnectionObject());
        $this->assertInstanceOf(ArrayList::class, $tasks);
    }

    public function testTasksThrowServerException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        $tasks = Admin::tasks($this->getConnectionObject($mock));
    }

    public function testFlushWal()
    {
        $result = Admin::flushWal($this->getConnectionObject());
        $this->assertIsBool($result);
    }

    public function testFlushWalThrowServerException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        $tasks = Admin::flushWal($this->getConnectionObject($mock));
    }
}
