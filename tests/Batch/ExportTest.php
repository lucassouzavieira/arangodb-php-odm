<?php


namespace Unit\Batch;

use Unit\TestCase;
use ArangoDB\Batch\Export;
use ArangoDB\Cursor\ExportCursor;
use ArangoDB\Cursor\Contracts\CursorInterface;

class ExportTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->getConnectionObject()->getDatabase()->dropCollection('test_cursor_coll');
        parent::tearDown();
    }

    public function testCollectionReturnsExportCursor()
    {
        $connection = $this->getConnectionObject();
        $collection = $connection->getDatabase()->createCollection('test_cursor_coll');
        $cursor = Export::collection($connection, 'test_cursor_coll');

        $this->assertInstanceOf(CursorInterface::class, $cursor);
        $this->assertInstanceOf(ExportCursor::class, $cursor);
    }
}
