<?php


namespace Unit\Collection;

use Unit\TestCase;
use GuzzleHttp\Psr7\Response;
use ArangoDB\Database\Database;
use ArangoDB\Collection\Collection;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Exceptions\DatabaseException;

class CollectionTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testConstructor()
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertObjectHasAttribute('connection', $collection);
    }

    public function testGetDatabase()
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->assertInstanceOf(Database::class, $collection->getDatabase());
    }

    public function testGetter()
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('any', $collection->name);

        $this->assertFalse($collection->waitForSync);
        $this->assertTrue($collection->doCompact);
        $this->assertNull($collection->id);

        $this->assertNull($collection->randomProperty);
    }

    public function testSetter()
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('any', $collection->name);
        $collection->waitForSync = true;
        $collection->doCompact = false;
        $collection->name = 'newAny';

        $this->assertNull($collection->id);
        $this->assertTrue($collection->waitForSync);
        $this->assertFalse($collection->doCompact);
        $this->assertEquals('newAny', $collection->name);

        $this->assertNull($collection->randomProperty);
    }

    public function testSetterThrowException()
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->expectException(\Exception::class);
        $collection->randomProperty = true;
    }

    public function testToString()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $this->assertIsString((string)$collection);
    }

    public function testGetName()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('we_are_the_champions', $collection->getName());
        $this->assertEquals($collection->name, $collection->getName());
    }

    public function testGetId()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase());
        $this->assertNull($collection->getId());

        $collection->save();
        $this->assertIsString($collection->getId());

        $this->assertTrue($collection->drop());
    }

    public function testGetStatus()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase());
        $this->assertEquals(0, $collection->getStatus());
    }

    public function testGetDescription()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('unknown', $collection->getStatusDescription());
    }

    public function testIsSystem()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase());
        $this->assertFalse($collection->isSystem());

        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $this->assertTrue($collection->isSystem());
    }

    public function testGetAttributes()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $this->assertIsArray($collection->getAttributes());
        $this->assertTrue($collection->getAttributes()['isSystem']);
    }

    public function testJsonSerialize()
    {
        $collection = new Collection('we_are_the_champions', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $this->assertJson(json_encode($collection));
    }

    public function testSave()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        // Check if collection is created.
        $this->assertNull($collection->getId());

        $this->assertTrue($collection->save());
        $this->assertIsString($collection->getId());
        $this->assertTrue($collection->drop());
    }

    public function testSaveThrowDatabaseException()
    {
        // Mock error
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = new Database($this->getConnectionObject($mock));
        $collection = new Collection('test_save_coll', $db);

        $this->expectException(DatabaseException::class);
        $collection->save();
    }

    public function testDrop()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        // Create.
        $this->assertNull($collection->getId());
        $collection->save();
        $this->assertIsString($collection->getId());

        $this->assertTrue($db->hasCollection('test_save_coll'));
        // drop
        $collection->drop();
        $this->assertFalse($db->hasCollection('test_save_coll'));
    }

    public function testDropThrowDatabaseException()
    {
        // Mock error
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = new Database($this->getConnectionObject($mock));
        $collection = new Collection('test_save_coll', $db);

        $this->expectException(DatabaseException::class);
        $collection->drop();
    }

    public function testRename()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_first_name', $db);

        // Check if collection is created.
        $this->assertTrue($collection->save());

        // Rename
        $this->assertTrue($collection->rename('test_snd_name'));

        // Check rename
        $this->assertFalse($db->hasCollection('test_first_name'));
        $this->assertTrue($db->hasCollection('test_snd_name'));

        $collection = $db->getCollection('test_snd_name');
        $this->assertEquals('test_snd_name', $collection->getName());
        $this->assertTrue($collection->drop());
    }

    public function testRenameThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = new Database($this->getConnectionObject($mock));
        $collection = new Collection('test_first_name', $db);

        // Check if collection is created.
        $this->assertTrue($collection->save());

        // Rename
        $this->expectException(DatabaseException::class);
        $this->assertTrue($collection->rename('test_snd_name'));
    }

    public function testRecalculateCount()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_first_name', $db);

        // Check if collection is created.
        $this->assertTrue($collection->save());

        // Recalculate
        $this->assertIsBool($collection->recalculateCount());
        $this->assertTrue($collection->drop());
    }

    public function testRecalculateCountThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = new Database($this->getConnectionObject($mock));
        $collection = new Collection('test_first_name', $db);

        // Check if collection is created.
        $this->assertTrue($collection->save());

        // Recalculate
        $this->expectException(DatabaseException::class);
        $this->assertIsBool($collection->recalculateCount());
    }

    public function testCount()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_first_name', $db);

        // Check if collection is created.
        $this->assertTrue($collection->save());

        // Count
        $this->assertEquals(0, $collection->count());
        $this->assertTrue($collection->drop());

        // TODO Make tests add with documents
    }

    public function testCountThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = new Database($this->getConnectionObject($mock));
        $collection = new Collection('test_first_name', $db);

        // Recalculate
        $this->expectException(DatabaseException::class);
        $this->assertIsBool($collection->count());
    }

    public function testLoad()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_first_name', $db, ['status' => 2]);

        $this->assertEquals(2, $collection->getStatus());

        // Check if collection is created.
        // After creation, ArangoDB server usually loads the collection
        $this->assertTrue($collection->save());

        // Load
        $this->assertTrue($collection->load());
        $this->assertEquals(3, $collection->getStatus()); // Check loaded status.

        $this->assertTrue($collection->drop());
    }

    public function testLoadThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = new Database($this->getConnectionObject($mock));
        $collection = new Collection('test_first_name', $db);

        // Load
        $this->expectException(DatabaseException::class);
        $this->assertTrue($collection->load());
    }

    public function testUnload()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_first_name', $db);

        // Check if collection is created.
        // After creation, ArangoDB server usually loads the collection
        $this->assertTrue($collection->save());

        // Unload
        $this->assertTrue($collection->unload());
        $this->assertEquals(2, $collection->getStatus()); // Check loaded status.

        $this->assertTrue($collection->drop());
    }

    public function testUnloadThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = new Database($this->getConnectionObject($mock));
        $collection = new Collection('test_first_name', $db);

        // Unload
        $this->expectException(DatabaseException::class);
        $this->assertTrue($collection->unload());
    }

    public function testGetChecksum()
    {
        $db = new Database($this->getConnectionObject());
        $coll1 = new Collection('test_first', $db, ['checksum' => '7854980051561']);
        $coll2 = new Collection('test_snd', $db);

        // Check if collection is created.
        // After creation, ArangoDB server usually loads the collection
        $this->assertTrue($coll1->save());
        $this->assertTrue($coll2->save());

        // Get checksum
        $this->assertEquals("7854980051561", $coll1->getChecksum()); // Empty collection.
        $this->assertEquals("0", $coll2->getChecksum()); // Empty collection.

        $this->assertTrue($coll1->drop());
        $this->assertTrue($coll2->drop());
    }

    public function testGetChecksumThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = new Database($this->getConnectionObject($mock));
        $collection = new Collection('test_first_name', $db);

        // Get checksum
        $this->expectException(DatabaseException::class);
        $this->assertEquals('0', $collection->getChecksum());
    }

    public function testGetRevision()
    {
        $db = new Database($this->getConnectionObject());
        $coll1 = new Collection('test_first', $db, ['revision' => '7854980051561']);
        $coll2 = new Collection('test_snd', $db);

        // Check if collection is created.
        // After creation, ArangoDB server usually loads the collection
        $this->assertTrue($coll1->save());
        $this->assertTrue($coll2->save());

        // Get checksum
        $this->assertEquals("7854980051561", $coll1->getRevision()); // Empty collection.
        $this->assertEquals("0", $coll2->getRevision()); // Empty collection.

        $this->assertTrue($coll1->drop());
        $this->assertTrue($coll2->drop());
    }

    public function testGetRevisionThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = new Database($this->getConnectionObject($mock));
        $collection = new Collection('test_first_name', $db);

        // Get checksum
        $this->expectException(DatabaseException::class);
        $this->assertEquals('0', $collection->getRevision());
    }

    public function testIsNew()
    {
        $db = new Database($this->getConnectionObject());
        $coll1 = $db->createCollection('test_first');
        $coll2 = new Collection('test_snd', $db);

        // Check 'isNew' returns
        $this->assertFalse($coll1->isNew());
        $this->assertTrue($coll2->isNew());

        $this->assertTrue($coll2->save());

        // After creations, 'isNew' must be false.
        $this->assertFalse($coll2->isNew());

        $this->assertTrue($coll1->drop());
        $this->assertTrue($coll2->drop());
    }
}
