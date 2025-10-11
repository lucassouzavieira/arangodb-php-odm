<?php

namespace Unit\Collection;

use ArangoDB\Collection\Index\InvertedIndex;
use ArangoDB\Collection\KeyType;
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

    public function testConstructor(): void
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertObjectHasProperty('connection', $collection);
    }

    public function testGetDatabase(): void
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->assertInstanceOf(Database::class, $collection->getDatabase());
    }

    public function testGetter(): void
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('any', $collection->name);

        $this->assertFalse($collection->waitForSync);
        $this->assertNull($collection->id);

        $this->assertNull($collection->randomProperty);
    }

    public function testSetter(): void
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('any', $collection->name);
        $collection->waitForSync = true;
        $collection->name = 'newAny';

        $this->assertNull($collection->id);
        $this->assertTrue($collection->waitForSync);
        $this->assertEquals('newAny', $collection->name);

        $this->assertNull($collection->randomProperty);
    }

    public function testSetterThrowException(): void
    {
        $collection = new Collection('any', $this->getConnectionObject()->getDatabase());
        $this->expectException(\Exception::class);
        $collection->randomProperty = true;
    }

    public function testToString(): void
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $this->assertIsString((string)$collection);
    }

    public function testGetName(): void
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('testing_collection_coll', $collection->getName());
        $this->assertEquals($collection->name, $collection->getName());
    }

    public function testGetId(): void
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase());
        $this->assertNull($collection->getId());

        $collection->save();
        $this->assertIsString($collection->getId());
        $this->assertTrue($collection->drop());
    }

    public function testGetStatus(): void
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase());
        $this->assertEquals(0, $collection->getStatus());
    }

    public function testGetDescription(): void
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase());
        $this->assertEquals('unknown', $collection->getStatusDescription());
    }

    public function testIsSystem(): void
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase());
        $this->assertFalse($collection->isSystem());

        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $this->assertTrue($collection->isSystem());
    }

    public function testGetAttributes(): void
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $this->assertIsArray($collection->getAttributes());
        $this->assertTrue($collection->getAttributes()['isSystem']);
    }

    public function testJsonSerialize(): void
    {
        $collection = new Collection('testing_collection_coll', $this->getConnectionObject()->getDatabase(), ['isSystem' => true]);
        $this->assertJson(json_encode($collection));
    }

    public function testGetGloballyUniqueId(): void
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        // Check if collection is created.
        $this->assertEmpty($collection->getGloballyUniqueId());

        $this->assertTrue($collection->save());

        $this->assertIsString($collection->getGloballyUniqueId());
        $this->assertTrue($collection->drop());
    }

    public function testAddFullTextIndex(): void
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

    public function testAddGeoSpatialIndex(): void
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

    public function testAddHashIndex(): void
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

    public function testAddPersistentIndex(): void
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

    public function testAddSkipListIndex(): void
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

    public function testAddTTLIndex(): void
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

    public function testAddInvertedIndex(): void
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());
        $this->assertCount(1, $collection->getIndexes());

        $index = new InvertedIndex(['complicated']);
        $this->assertTrue($collection->addIndex($index));

        $this->assertCount(2, $collection->getIndexes());
        $this->assertEquals('inverted', $collection->getIndexes()->last()->getType());
    }

    public function testAddIndexThrowDatabaseException(): void
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

    public function testAddIndexOnNewCollectionReturnFalse(): void
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $index = new FullTextIndex(['complicated'], 3);
        $this->assertFalse($collection->addIndex($index));
    }

    public function testDropIndex(): void
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

    public function testDropIndexOnNewCollectionReturnFalse(): void
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $index = new FullTextIndex(['complicated'], 3);
        $this->assertFalse($collection->dropIndex($index));
    }

    public function testDropNewIndexReturnFalse(): void
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());
        $this->assertCount(1, $collection->getIndexes());

        // Index not created before
        $index = new FullTextIndex(['complicated'], 3);
        $this->assertFalse($collection->dropIndex($index));
    }


    public function testDropNonExistentIndexReturnFalse(): void
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

    public function testDropIndexThrowDatabaseException(): void
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

        // Try to drop a non-existent index
        $this->expectException(DatabaseException::class);
        $collection->dropIndex($list->last());
    }

    public function testGetIndexes(): void
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

    public function testGetIndexesThrowDatabaseException(): void
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

    public function testAll(): void
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        // False for new born collections
        $this->assertFalse($collection->all());
        $this->assertTrue($collection->save());
        $this->assertInstanceOf(CollectionCursor::class, $collection->all());
    }

    public function testFindByKey(): void
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

    public function testFindByKeyReturnVertex(): void
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

    public function testFindByKeyReturnFalse(): void
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        $this->assertTrue($collection->save());
        $document = new Document(['document' => 'testing'], $collection);
        $document->save();

        $doc = $collection->findByKey("unknown");
        $this->assertFalse($doc);
    }

    public function testFindByKeyThrowDatabaseException(): void
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

    public function testSave(): void
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db);

        // Check if collection is created.
        $this->assertNull($collection->getId());

        $this->assertTrue($collection->save());
        $this->assertIsString($collection->getId());
        $this->assertTrue($collection->drop());
    }

    public function testSaveWithNonDefaultOptions(): void
    {
        $keyOptions = [
            'allowUserKeys' => false,
            'type' => KeyType::UUID,
        ];

        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_save_coll', $db, ['keyOptions' => $keyOptions]);

        // Check if collection is created.
        $this->assertNull($collection->getId());

        $this->assertTrue($collection->save());
        $this->assertIsString($collection->getId());
        $this->assertTrue($collection->drop());
    }

    public function testSaveThrowDatabaseException(): void
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

    public function testDrop(): void
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

    public function testDropReturnFalse(): void
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('coll_to_drop', $db);
        // drop
        $this->assertFalse($collection->drop());
    }

    public function testDropThrowDatabaseException(): void
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

    public function testTruncate(): void
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

    public function testTruncateThrowDatabaseException(): void
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

    public function testRename(): void
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

    public function testRenameThrowDatabaseException(): void
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

    public function testRecalculateCount(): void
    {
        $db = new Database($this->getConnectionObject());
        $collection = new Collection('test_first_name', $db);

        // Check if collection is created.
        $this->assertTrue($collection->save());

        // Recalculate
        $this->assertIsBool($collection->recalculateCount());
        $this->assertTrue($collection->drop());
    }

    public function testRecalculateCountThrowDatabaseException(): void
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

    public function testCount(): void
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

    public function testCountThrowDatabaseException(): void
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

    public function testGetChecksum(): void
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

    public function testGetChecksumThrowDatabaseException(): void
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

    public function testGetRevision(): void
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

    public function testGetRevisionThrowDatabaseException(): void
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

    public function testIsNew(): void
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

    public function testIsGraph(): void
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
