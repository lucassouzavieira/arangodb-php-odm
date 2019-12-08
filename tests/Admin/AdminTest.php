<?php

namespace Unit\Admin;

use Unit\TestCase;
use ArangoDB\Auth\User;
use ArangoDB\Admin\Admin;
use GuzzleHttp\Psr7\Response;
use ArangoDB\Admin\Task\Task;
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

    public function testUser()
    {
        $user = new User([
            'user' => 'tester',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ], $this->getConnectionObject());

        // Create user.
        $user->save();

        // Check on server.
        $userObj = Admin::user($this->getConnectionObject(), 'tester');
        $this->assertInstanceOf(User::class, $userObj);
        $this->assertEquals($user->toArray(), $userObj->toArray());
        $this->assertTrue($user->delete());
    }

    public function testUserReturnFalseForNonExistingUser()
    {
        $user = Admin::user($this->getConnectionObject(), 'tester');
        $this->assertFalse($user);
    }

    public function testUserThrowServerException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        $user = Admin::user($this->getConnectionObject($mock), 'tester');
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
        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command, $this->getConnectionObject());

        // Create task
        $tasks = Admin::tasks($this->getConnectionObject());
        $this->assertInstanceOf(ArrayList::class, $tasks);
        $this->assertEquals(0, count($tasks));

        $this->assertTrue($task->save());

        $tasks = Admin::tasks($this->getConnectionObject());
        $this->assertEquals(1, count($tasks));

        // Delete Task
        $this->assertTrue($task->delete());
    }

    public function testTasksThrowServerException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        Admin::tasks($this->getConnectionObject($mock));
    }

    public function testTime()
    {
        $time = Admin::time($this->getConnectionObject());
        $this->assertIsFloat($time);
    }

    public function testTimeThrowServerException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        Admin::time($this->getConnectionObject($mock));
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
        Admin::flushWal($this->getConnectionObject($mock));
    }

    public function testWalProperties()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'allowOversizeEntries' => true,
                'logfileSize' => 128,
                'historicLogfiles' => 512,
                'reserveLogfiles' => 512,
                'syncInterval' => 100,
                'throttleWait' => 1000,
                'throttleWhenPending' => 0
            ]))
        ]);

        $result = Admin::walProperties($this->getConnectionObject($mock));
        $this->assertIsArray($result);
        $this->assertArrayHasKey('allowOversizeEntries', $result);
        $this->assertArrayHasKey('logfileSize', $result);
        $this->assertArrayHasKey('throttleWhenPending', $result);
        $this->assertArrayHasKey('historicLogfiles', $result);
    }

    public function testWalPropertiesThrowServerException()
    {
        $mock = new MockHandler([
            new Response(405, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        Admin::walProperties($this->getConnectionObject($mock));
    }

    public function testWalPropertiesWhenNotImplemented()
    {
        $mock = new MockHandler([
            new Response(501, [], json_encode([]))
        ]);

        $this->expectException(ServerException::class);
        Admin::walProperties($this->getConnectionObject($mock));
    }

    public function testWalTransactions()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'runningTransactions' => 0,
                'minLastCollected' => null,
                'minLastSealed' => null
            ]))
        ]);

        $result = Admin::walTransactions($this->getConnectionObject($mock));
        $this->assertIsArray($result);
        $this->assertArrayHasKey('runningTransactions', $result);
        $this->assertArrayHasKey('minLastCollected', $result);
        $this->assertArrayHasKey('minLastSealed', $result);
    }

    public function testWalTransactionsThrowServerException()
    {
        $mock = new MockHandler([
            new Response(405, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        Admin::walTransactions($this->getConnectionObject($mock));
    }

    public function testWalTransactionsWhenNotImplemented()
    {
        $this->expectException(ServerException::class);
        Admin::walProperties($this->getConnectionObject());
    }
}
