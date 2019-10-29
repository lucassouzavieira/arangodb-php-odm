<?php


namespace Unit\Batch;

use ArangoDB\Exceptions\ServerException;
use Unit\TestCase;
use ArangoDB\Batch\Import;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;

class ImportTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->getConnectionObject()->getDatabase()->dropCollection('world_cup_editions');
        parent::tearDown();
    }

    public function getContent($file = "world_cup_import.txt")
    {
        return file_get_contents(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "files/$file");
    }

    public function testImportFromJsonDocuments()
    {
        $contents = $this->getContent();
        $this->getConnectionObject()->getDatabase()->createCollection('world_cup_editions');
        $result = Import::importJsonDocuments($this->getConnectionObject(), 'world_cup_editions', $contents);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('created', $result);
        $this->assertEquals(21, $result['created']);
    }

    public function testImportFromJsonDocumentsThrowServerExceptionOnNonExistentCollection()
    {
        $contents = $this->getContent();
        $this->expectException(ServerException::class);
        $result = Import::importJsonDocuments($this->getConnectionObject(), 'world_cup_editions', $contents);
    }

    public function testImportFromJsonDocumentsThrowServerException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $contents = $this->getContent();
        $this->expectException(ServerException::class);
        $result = Import::importJsonDocuments($this->getConnectionObject($mock), 'world_cup_editions', $contents);
    }

    public function testImportFromJsonDocumentsThrowServerExceptionOnDatabaseExceptionThrown()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $contents = $this->getContent();
        $this->expectException(ServerException::class);
        $result = Import::importJsonDocuments($this->getConnectionObject($mock), 'world_cup_editions', $contents);
    }

    public function testImportFromArrayDocuments()
    {
        $contents = $this->getContent("world_cup_array_import.txt");
        $this->getConnectionObject()->getDatabase()->createCollection('world_cup_editions');
        $result = Import::importArrayDocuments($this->getConnectionObject(), 'world_cup_editions', $contents);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('created', $result);
        $this->assertEquals(21, $result['created']);
    }

    public function testImportFromArrayDocumentsThrowServerExceptionOnNonExistentCollection()
    {
        $contents = $this->getContent("world_cup_array_import.txt");
        $this->expectException(ServerException::class);
        $result = Import::importArrayDocuments($this->getConnectionObject(), 'world_cup_editions', $contents);
    }

    public function testImportFromArrayDocumentsThrowServerException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $contents = $this->getContent("world_cup_array_import.txt");
        $this->expectException(ServerException::class);
        $result = Import::importArrayDocuments($this->getConnectionObject($mock), 'world_cup_editions', $contents);
    }

    public function testImportFromArrayDocumentsThrowServerExceptionOnDatabaseExceptionThrown()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $contents = $this->getContent("world_cup_array_import.txt");
        $this->expectException(ServerException::class);
        $result = Import::importArrayDocuments($this->getConnectionObject($mock), 'world_cup_editions', $contents);
    }
}
