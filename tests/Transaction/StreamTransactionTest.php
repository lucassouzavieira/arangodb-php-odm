<?php


namespace Unit\Transaction;

use Unit\TestCase;
use GuzzleHttp\Psr7\Response;
use ArangoDB\Document\Document;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Exceptions\DatabaseException;
use ArangoDB\Transaction\StreamTransaction;
use ArangoDB\Exceptions\TransactionException;

class StreamTransactionTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->getConnectionObject()->getDatabase()->dropCollection('fighter_jets');
        parent::tearDown();
    }

    public function testConstructorThrowTransactionException()
    {
        $options = [
            'collections' => []
        ];

        $this->expectException(TransactionException::class);
        $transaction = new StreamTransaction($this->getConnectionObject(), $options);
    }

    public function testBegin()
    {
        $this->getConnectionObject()->getDatabase()->createCollection('fighter_jets');
        $options = [
            'collections' => [
                'read' => [
                    'fighter_jets'
                ],
                'write' => [
                    'fighter_jets'
                ]
            ],

        ];

        $connection = $this->getConnectionObject();
        $transaction = new StreamTransaction($connection, $options);
        $transaction->begin();

        // Transaction was started on server
        $this->assertIsString($transaction->getTransactionId());
        $this->assertEquals('running', $transaction->getTransactionStatus());

        // Connection must have the transaction header.
        $this->assertCount(1, $connection->getDefaultHeaders());
        $this->assertArrayHasKey('x-arango-trx-id', $connection->getDefaultHeaders());
    }

    public function testBeginThrowTransactionException()
    {
        $options = [
            'collections' => [
                'read' => [
                    'fighter_jets' // Nonexistent collection
                ]
            ],

        ];

        $transaction = new StreamTransaction($this->getConnectionObject(), $options);
        $this->expectException(TransactionException::class);
        $transaction->begin();
    }

    public function testCommit()
    {
        $this->getConnectionObject()->getDatabase()->createCollection('fighter_jets');
        $options = [
            'collections' => [
                'write' => [
                    'fighter_jets'
                ]
            ],

        ];

        $connection = $this->getConnectionObject();

        // Begin transaction.
        $transaction = new StreamTransaction($connection, $options);
        $transaction->begin();

        // transaction was started on server
        $this->assertIsString($transaction->getTransactionId());
        $this->assertEquals('running', $transaction->getTransactionStatus());
        $this->assertCount(1, $connection->getDefaultHeaders());
        $this->assertArrayHasKey('x-arango-trx-id', $connection->getDefaultHeaders());

        // Perform some operations
        $collection = $connection->getDatabase()->getCollection('fighter_jets');
        $viper = new Document(['model' => 'F-16 Fighting Falcon', 'status' => 'In service', 'origin' => 'United States'], $collection);
        $gripen = new Document(['model' => 'JAS 39 Gripen', 'status' => 'In service', 'origin' => 'Sweden'], $collection);
        $viper->save();
        $gripen->save();

        // Commit transaction
        $transaction->commit();
        $this->assertIsString($transaction->getTransactionId());
        $this->assertEquals('committed', $transaction->getTransactionStatus());
        $this->assertCount(0, $connection->getDefaultHeaders());

        // Assert collection
        $this->assertEquals(2, $collection->count());
        $cursor = $collection->all();
        $this->assertEquals('F-16 Fighting Falcon', $cursor->current()->model);
    }

    public function testCommitThrowTransactionExceptionWithoutCallToBegin()
    {
        $this->getConnectionObject()->getDatabase()->createCollection('fighter_jets');
        $options = [
            'collections' => [
                'write' => [
                    'fighter_jets'
                ]
            ],

        ];

        $connection = $this->getConnectionObject();
        $transaction = new StreamTransaction($connection, $options);
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage("Transaction not started. Use 'begin' method to start transaction");
        $transaction->commit();
    }

    public function testCommitThrowDatabaseExceptionOnIllegalOperation()
    {
        $this->getConnectionObject()->getDatabase()->createCollection('fighter_jets');
        $options = [
            'collections' => [
                'read' => [
                    'fighter_jets' // Set only read on collection
                ]
            ],

        ];

        $connection = $this->getConnectionObject();

        // Begin transaction.
        $transaction = new StreamTransaction($connection, $options);
        $transaction->begin();

        // transaction was started on server
        $this->assertIsString($transaction->getTransactionId());
        $this->assertEquals('running', $transaction->getTransactionStatus());
        $this->assertCount(1, $connection->getDefaultHeaders());
        $this->assertArrayHasKey('x-arango-trx-id', $connection->getDefaultHeaders());

        // Perform some illegal operations - write not set for this transaction
        // Must throw an exception
        $collection = $connection->getDatabase()->getCollection('fighter_jets');
        $viper = new Document(['model' => 'F-16 Fighting Falcon', 'status' => 'In service', 'origin' => 'United States'], $collection);
        $this->expectException(DatabaseException::class);
        $viper->save();
    }

    public function testCommitThrowTransactionExceptionOnBadResponse()
    {
        $options = [
            'collections' => [
                'read' => [
                    'fighter_jets'
                ]
            ],

        ];
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => ['id' => '435112', 'status' => 'running']])),
            new Response(400, [], json_encode($this->mockServerError()))
        ]);
        $connection = $this->getConnectionObject($mock);

        // Begin transaction.
        $transaction = new StreamTransaction($connection, $options);
        $transaction->begin();

        // Expect some exception
        $this->expectException(TransactionException::class);
        $transaction->commit();
    }

    public function testAbort()
    {
        $this->getConnectionObject()->getDatabase()->createCollection('fighter_jets');
        $options = [
            'collections' => [
                'write' => [
                    'fighter_jets'
                ]
            ],

        ];

        $connection = $this->getConnectionObject();

        // Begin transaction.
        $transaction = new StreamTransaction($connection, $options);
        $transaction->begin();

        // transaction was started on server
        $this->assertIsString($transaction->getTransactionId());
        $this->assertEquals('running', $transaction->getTransactionStatus());
        $this->assertCount(1, $connection->getDefaultHeaders());
        $this->assertArrayHasKey('x-arango-trx-id', $connection->getDefaultHeaders());

        // Perform some operations
        $collection = $connection->getDatabase()->getCollection('fighter_jets');
        $viper = new Document(['model' => 'F-16 Fighting Falcon', 'status' => 'In service', 'origin' => 'United States'], $collection);
        $gripen = new Document(['model' => 'JAS 39 Gripen', 'status' => 'In service', 'origin' => 'Sweden'], $collection);
        $viper->save();
        $gripen->save();

        // Abort transaction
        $transaction->abort();
        $this->assertIsString($transaction->getTransactionId());
        $this->assertEquals('aborted', $transaction->getTransactionStatus());
        $this->assertCount(0, $connection->getDefaultHeaders());

        // Assert collection has no documents
        $this->assertEquals(0, $collection->count());
    }

    public function testAbortThrowTransactionExceptionWithoutCallToBegin()
    {
        $this->getConnectionObject()->getDatabase()->createCollection('fighter_jets');
        $options = [
            'collections' => [
                'write' => [
                    'fighter_jets'
                ]
            ],

        ];

        $connection = $this->getConnectionObject();
        $transaction = new StreamTransaction($connection, $options);
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage("Transaction not started. Use 'begin' method to start transaction");
        $transaction->abort();
    }

    public function testAbortThrowTransactionExceptionOnBadResponse()
    {
        $options = [
            'collections' => [
                'read' => [
                    'fighter_jets'
                ]
            ],

        ];
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => ['id' => '435112', 'status' => 'running']])),
            new Response(400, [], json_encode($this->mockServerError()))
        ]);
        $connection = $this->getConnectionObject($mock);

        // Begin transaction.
        $transaction = new StreamTransaction($connection, $options);
        $transaction->begin();

        // Expect some exception
        $this->expectException(TransactionException::class);
        $transaction->abort();
    }
}
