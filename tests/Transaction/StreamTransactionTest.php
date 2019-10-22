<?php


namespace Unit\Transaction;

use Unit\TestCase;
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

}