<?php


namespace Unit\Collection;

use ArangoDB\Admin\Server;
use Unit\TestCase;
use GuzzleHttp\Psr7\Response;
use ArangoDB\Document\Vertex;
use ArangoDB\Database\Database;
use ArangoDB\Document\Document;
use ArangoDB\Collection\Collection;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Collection\Index\Index;
use ArangoDB\Cursor\CollectionCursor;
use ArangoDB\Collection\Index\TTLIndex;
use ArangoDB\Collection\Index\HashIndex;
use ArangoDB\Collection\Index\FullTextIndex;
use ArangoDB\Collection\Index\SkipListIndex;
use ArangoDB\Collection\Index\PersistentIndex;
use ArangoDB\Collection\Index\GeoSpatialIndex;
use ArangoDB\Exceptions\Database\DatabaseException;

class CollectionTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->getConnectionObject()->getDatabase()->dropCollection('test_coll');
        $this->getConnectionObject()->getDatabase()->dropCollection('test_save_coll');
        $this->getConnectionObject()->getDatabase()->dropCollection('testing_collection_coll');
        parent::tearDown();
    }

    public function testConstructor()
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertObjectHasProperty('connection', $collection);
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
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $this->assertIsString((string)$collection);
    }

    public function testGetName()
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('testing_collection_coll', $collection->getName());
        $this->assertEquals($collection->name, $collection->getName());
    }

    public function testGetId()
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase());
        $this->assertNull($collection->getId());

        $collection->save();
        $this->assertIsString($collection->getId());
        $this->assertTrue($collection->drop());
    }

    public function testGetStatus()
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase());
        $this->assertEquals(0, $collection->getStatus());
    }

    public function testGetDescription()
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('unknown', $collection->getStatusDescription());
    }

    public function testIsSystem()
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase());
        $this->assertFalse($collection->isSystem());

        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $this->assertTrue($collection->isSystem());
    }

    public function testGetAttributes()
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $this->assertIsArray($collection->getAttributes());
        $this->assertTrue($collection->getAttributes()['isSystem']);
    }

    public function testJsonSerialize()
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $this->assertJson(json_encode($collection));
    }

    public function testGetGloballyUniqueId()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        // Check if collection is created.
        $this->assertEmpty($collection->getGloballyUniqueId());

        $this->assertTrue($collection->save());

        $this->assertIsString($collection->getGloballyUniqueId());
        $this->assertTrue($collection->drop());
    }

    public function testAddFullTextIndex()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());
        $this->assertCount(1, $collection->getIndexes());

        $index = new FullTextIndex(['complicated'], 3);
        $this->assertTrue($collection->addIndex($index));

        $this->assertCount(2, $collection->getIndexes());
        $this->assertEquals('fulltext', $collection->getIndexes()->last()->getType());
    }

    public function testAddGeoSpatialIndex()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());
        $this->assertCount(1, $collection->getIndexes());

        $index = new GeoSpatialIndex(['complicated'], true);
        $this->assertTrue($collection->addIndex($index));

        $this->assertCount(2, $collection->getIndexes());
        $this->assertEquals('geo', $collection->getIndexes()->last()->getType());
    }

    public function testAddHashIndex()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());
        $this->assertCount(1, $collection->getIndexes());

        $index = new HashIndex(['complicated']);
        $this->assertTrue($collection->addIndex($index));

        $this->assertCount(2, $collection->getIndexes());
        $this->assertEquals('hash', $collection->getIndexes()->last()->getType());
    }

    public function testAddPersistentIndex()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());
        $this->assertCount(1, $collection->getIndexes());

        $index = new PersistentIndex(['complicated']);
        $this->assertTrue($collection->addIndex($index));

        $this->assertCount(2, $collection->getIndexes());
        $this->assertEquals('persistent', $collection->getIndexes()->last()->getType());
    }

    public function testAddSkipListIndex()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());
        $this->assertCount(1, $collection->getIndexes());

        $index = new SkipListIndex(['complicated']);
        $this->assertTrue($collection->addIndex($index));

        $this->assertCount(2, $collection->getIndexes());
        $this->assertEquals('skiplist', $collection->getIndexes()->last()->getType());
    }

    public function testAddTTLIndex()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());
        $this->assertCount(1, $collection->getIndexes());

        $index = new TTLIndex(['complicated']);
        $this->assertTrue($collection->addIndex($index));

        $this->assertCount(2, $collection->getIndexes());
        $this->assertEquals('ttl', $collection->getIndexes()->last()->getType());
    }

    public function testAddIndexThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = new Database($this->getConnectionObject($mock));
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());

        $index = new TTLIndex(['complicated']);
        $this->expectException(DatabaseException::class);
        $collection->addIndex($index);
    }

    public function testAddIndexOnNewCollectionReturnFalse()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $index = new FullTextIndex(['complicated'], 3);
        $this->assertFalse($collection->addIndex($index));
    }

    public function testDropIndex()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());
        $this->assertCount(1, $collection->getIndexes());

        $index = new FullTextIndex(['complicated'], 3);
        $this->assertTrue($collection->addIndex($index));

        $list = $collection->getIndexes();
        $this->assertCount(2, $collection->getIndexes());
        $this->assertEquals('fulltext', $list->last()->getType());

        // Drop
        $fulltext = $list->last();
        $this->assertTrue($collection->dropIndex($fulltext));

        // Must have only 'primary' index
        $this->assertCount(1, $collection->getIndexes());
    }

    public function testDropIndexOnNewCollectionReturnFalse()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $index = new FullTextIndex(['complicated'], 3);
        $this->assertFalse($collection->dropIndex($index));
    }

    public function testDropNewIndexReturnFalse()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());
        $this->assertCount(1, $collection->getIndexes());

        // Index not created before
        $index = new FullTextIndex(['complicated'], 3);
        $this->assertFalse($collection->dropIndex($index));
    }


    public function testDropNonExistentIndexReturnFalse()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());

        $index = new FullTextIndex(['complicated'], 3);
        $collection->addIndex($index);

        $list = $collection->getIndexes();
        // Drop
        $fulltext = $list->last();
        $this->assertTrue($collection->dropIndex($fulltext));

        // Try to drop an non-existent index
        $this->assertFalse($collection->dropIndex($fulltext));
    }

    public function testDropIndexThrowDatabaseException()
    {
        $index = new FullTextIndex(['complicated'], 3);
        $mocked = [
            'indexes' => [
                [
                    'id' => 'coll/1',
                    'name' => 'primary',
                    'type' => 'primary',
                    'sparse' => false,
                    'unique' => true,
                    'fields' => [
                        '_key'
                    ]
                ],
                [
                    'id' => 'coll/2',
                    'name' => 'idx_1646382074382254082',
                    'type' => 'fulltext',
                    'minLength' => 3,
                    'sparse' => true,
                    'unique' => true,
                    'fields' => [
                        'complicated'
                    ]

                ]
            ]
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode($mocked)),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = new Database($this->getConnectionObject($mock));
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());

        $collection->addIndex($index);

        $list = $collection->getIndexes();

        // Try to drop an non-existent index
        $this->expectException(DatabaseException::class);
        $collection->dropIndex($list->last());
    }

    public function testGetIndexes()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        // On new collections return an empty array list
        $list = $collection->getIndexes();
        $this->assertCount(0, $list);

        // Save collection
        $this->assertTrue($collection->save());
        $this->assertCount(1, $collection->getIndexes());

        $index = new Index('fulltext', ['complicated']);
        $this->assertTrue($collection->addIndex($index));

        $list = $collection->getIndexes();
        $this->assertCount(2, $collection->getIndexes());
        $this->assertEquals('fulltext', $list->last()->getType());

        // Drop
        $fulltext = $list->last();
        $this->assertTrue($collection->dropIndex($fulltext));

        // Must have only 'primary' index
        $this->assertCount(1, $collection->getIndexes());
    }

    public function testGetIndexesThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = new Database($this->getConnectionObject($mock));
        $collection = new Collection('test_save_coll', $db);

        $this->expectException(DatabaseException::class);
        $indexes = $collection->getIndexes();
    }

    public function testAll()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        // False for new born collections
        $this->assertFalse($collection->all());
        $this->assertTrue($collection->save());
        $this->assertInstanceOf(CollectionCursor::class, $collection->all());
    }

    public function testFindByKey()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());
        $document = new Document(['document' => 'testing'], $collection);
        $document->save();

        $key = $document->getKey();
        $doc = $collection->findByKey($key);

        $this->assertInstanceOf(Document::class, $doc);
        $this->assertArrayHasKey('document', $doc->toArray());
        $this->assertEquals('testing', $doc->toArray()['document']);
    }

    public function testFindByKeyReturnVertex()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());
        $document = new Document(['document' => 'testing'], $collection);
        $document->save();

        $key = $document->getKey();
        $doc = $collection->findByKey($key, true);

        $this->assertInstanceOf(Vertex::class, $doc);
        $this->assertArrayHasKey('document', $doc->toArray());
        $this->assertEquals('testing', $doc->toArray()['document']);
    }

    public function testFindByKeyReturnFalse()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());
        $document = new Document(['document' => 'testing'], $collection);
        $document->save();

        $doc = $collection->findByKey("unknown");
        $this->assertFalse($doc);
    }

    public function testFindByKeyThrowDatabaseException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $db = new Database($this->getConnectionObject($mock));
        $collection = new Collection('test_save_coll', $db);
        $this->expectException(DatabaseException::class);
        $collection->findByKey("unknown");
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

    public function testDropReturnFalse()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('coll_to_drop', $db);
        // drop
        $this->assertFalse($collection->drop());
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

    public function testTruncate()
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        // Create.
        $this->assertNull($collection->getId());
        $this->assertTrue($collection->save());

        $this->assertEquals(0, $collection->count());

        $doc1 = new Document(['hello' => 'Brazil'], $collection);
        $doc2 = new Document(['hello' => 'Germany'], $collection);
        $doc1->save();
        $doc2->save();

        $this->assertEquals(2, $collection->count());
        // Truncate
        $this->assertTrue($collection->truncate());
        $this->assertEquals(0, $collection->count());
        $collection->drop();
        $this->assertFalse($db->hasCollection('test_save_coll'));
    }

    public function testTruncateThrowDatabaseException()
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
        $collection->truncate();
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

        $this->assertFalse($db->hasCollection("test_first"));
        $this->assertFalse($db->hasCollection("test_snd"));

        $coll1 = new Collection('test_first', $db, ['revision' => '7854980051561']);
        $coll2 = new Collection('test_snd', $db);

        // Check if collection is created.
        // After creation, ArangoDB server usually loads the collection
        $this->assertTrue($coll1->save());
        $this->assertTrue($coll2->save());

        // Get checksum
        $this->assertEquals("7854980051561", $coll1->getRevision()); // Empty collection.

        $compare_to = "54"; // 3.8+ specific behaviors. Default revision number is 54.
        $this->assertEquals($compare_to, $coll2->getRevision()); // Empty collection.

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

    public function testIsGraph()
    {
        $db = new Database($this->getConnectionObject());
        $coll = $db->createCollection('test_graph');

        $this->assertFalse($coll->isGraph());

        $this->assertTrue($coll->drop());

        $db = new Database($this->getConnectionObject());
        $coll = $db->createCollection('test_graph', ['type' => 3]);

        $this->assertTrue($coll->isGraph());

        $this->assertTrue($coll->drop());
    }
}
