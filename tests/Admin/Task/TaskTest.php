<?php


namespace Unit\Admin\Task;

use Unit\TestCase;
use ArangoDB\Admin\Admin;
use GuzzleHttp\Psr7\Response;
use ArangoDB\Admin\Task\Task;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Exceptions\ServerException;

class TaskTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function tearDown(): void
    {
        $tasks = Admin::tasks($this->getConnectionObject());
        foreach ($tasks as $task) {
            $task->delete();
        }
    }

    public function getOptions()
    {
        return [
            'params' => [
                'city' => 'Rio de Janeiro',
                'status' => false,
            ],
            'offset' => 1,
            'period' => 30,
        ];
    }

    public function testConstruct()
    {
        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command);
        $this->assertInstanceOf(Task::class, $task);

        $task = new Task("myTask", $command, $this->getConnectionObject(), ['offset' => 1]);
        $this->assertInstanceOf(Task::class, $task);
    }

    public function testSave()
    {
        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command, $this->getConnectionObject());

        $this->assertEquals(0, count(Admin::tasks($this->getConnectionObject())));
        $this->assertTrue($task->save());
        $this->assertEquals(1, count(Admin::tasks($this->getConnectionObject())));

        $this->assertIsString($task->getId());
    }

    public function testSaveWithoutConnectionReturnFalse()
    {
        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command);

        $this->assertFalse($task->save());
    }

    public function testSaveThrowServerException()
    {
        // Bad command
        $command = "(function(params)(function(){\n require('@arangodb/foxx/queues/manager').manage();\n }params))(params);";
        $task = new Task("myTask", $command, $this->getConnectionObject());

        $this->expectException(ServerException::class);
        $this->assertTrue($task->save());
    }

    public function testSaveWithCustomId()
    {
        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command, $this->getConnectionObject());
        $task->setId('mycustom');

        $this->assertEquals(0, count(Admin::tasks($this->getConnectionObject())));
        $this->assertTrue($task->save());
        $this->assertEquals(1, count(Admin::tasks($this->getConnectionObject())));

        // Verify created task
        $tasks = Admin::tasks($this->getConnectionObject());
        $this->assertEquals('mycustom', $tasks->first()->getId());
    }

    public function testDelete()
    {
        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command, $this->getConnectionObject());

        // Create task
        $this->assertEquals(0, count(Admin::tasks($this->getConnectionObject())));
        $this->assertTrue($task->save());
        $this->assertEquals(1, count(Admin::tasks($this->getConnectionObject())));

        // Delete Task
        $this->assertTrue($task->delete());
        $this->assertEquals(0, count(Admin::tasks($this->getConnectionObject())));
    }

    public function testDeleteReturnFalseForNewBornTask()
    {
        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command, $this->getConnectionObject());

        // Try to delete a new task
        $this->assertFalse($task->delete());
        $this->assertEquals(0, count(Admin::tasks($this->getConnectionObject())));
    }

    public function testDeleteReturnFalseForNonexistentTask()
    {
        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command, $this->getConnectionObject(), ['id' => '154665']);

        // Try to delete an non-existent task
        $this->assertFalse($task->delete());
        $this->assertEquals(0, count(Admin::tasks($this->getConnectionObject())));
    }

    public function testDeleteThrowServerException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command, $this->getConnectionObject($mock), ['id' => '154665']);

        $this->expectException(ServerException::class);
        $this->assertFalse($task->delete());
    }

    public function testToArray()
    {
        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command, $this->getConnectionObject());

        $this->assertIsArray($task->toArray());
        $this->assertArrayHasKey('id', $task->toArray());
        $this->assertNull($task->toArray()['id']);
        $this->assertEquals('myTask', $task->toArray()['name']);
        $this->assertEquals($command, $task->toArray()['command']);

        $task->save();

        $this->assertNotNull($task->toArray()['id']);
    }

    public function testIsNew()
    {
        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command, $this->getConnectionObject());

        $this->assertTrue($task->isNew());
        $task->save();
        $this->assertFalse($task->isNew());
    }

    public function testGetId()
    {
        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command, $this->getConnectionObject());

        $this->assertNull($task->getId());
        $task->save();
        $this->assertIsString($task->getId());
    }

    public function testGetType()
    {
        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command, $this->getConnectionObject());

        $this->assertEquals('unknown', $task->getType());
        $task->save();
        $this->assertTrue(in_array($task->getType(), ['periodic', 'timed']));
    }

    public function testJsonSerialize()
    {
        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command, $this->getConnectionObject());
        $this->assertJson(json_encode($task));
    }

    public function testSetAndHasConnection()
    {
        $command = "(function(params){ (function(){\n require('@arangodb/foxx/queues/manager').manage();\n })(params)})(params);";
        $task = new Task("myTask", $command, null, ['id' => '451657']);

        $this->assertFalse($task->hasConnection());
        $task->setConnection($this->getConnectionObject());
        $this->assertTrue($task->hasConnection());
    }

    public function testSetAndGetCommand()
    {
        $command = "(function(params) { require('@arangodb').print(params); })(params)";
        $task = new Task("myTask", $command);

        $this->assertEquals($command, $task->getCommand());

        $newCommand = "(function() { require('@arangodb').print('Ola!'); })()";
        $task->setCommand($newCommand);

        $this->assertEquals($newCommand, $task->getCommand());
    }
}
