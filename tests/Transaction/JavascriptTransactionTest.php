<?php


namespace Unit\Transaction;

use Unit\TestCase;
use ArangoDB\Exceptions\TransactionException;
use ArangoDB\Transaction\JavascriptTransaction;

class JavascriptTransactionTest extends TestCase
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

        $action = "function () { var db = require('@arangodb').db; db.products.save({});  return db.products.count(); }";
        $this->expectException(TransactionException::class);
        $transaction = new JavascriptTransaction($this->getConnectionObject(), $action, $options);
    }

    public function testExecute()
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
            ]
        ];

        $action = "function () { var db = require('@arangodb').db; return db.fighter_jets.count(); }";
        $transaction = new JavascriptTransaction($this->getConnectionObject(), $action, $options);
        $result = $transaction->execute();
        $this->assertEquals(0, $result);
    }

    public function testExecuteThrowFromAction()
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
            ]
        ];

        $action = "function () { var db = require('@arangodb').db; throw 'JS error'; }";
        $transaction = new JavascriptTransaction($this->getConnectionObject(), $action, $options);
        $this->expectExceptionMessage('JS error');
        $result = $transaction->execute();
    }

    public function testExecuteThrowTransactionExceptionOnNonSettedReadTransactions()
    {
        $this->getConnectionObject()->getDatabase()->createCollection('fighter_jets');
        $options = [
            'collections' => [
                'read' => [
                    'fighter_jets'
                ]
            ]
        ];

        // Perform an write operation. With options given, this will throw an error.
        $action = "function () { var db = require('@arangodb').db; db.fighter_jets.save({});  return db.fighter_jets.count(); }";
        $transaction = new JavascriptTransaction($this->getConnectionObject(), $action, $options);
        $this->expectException(TransactionException::class);
        $transaction->execute();
    }

    public function testExecuteThrowTransactionException()
    {
        $options = [
            'collections' => [
                'write' => [
                    'fighter_jets' // Nonexistent collection
                ]
            ]
        ];

        $action = "function () { var db = require('@arangodb').db; db.fighter_jets.save({});  return db.fighter_jets.count(); }";
        $transaction = new JavascriptTransaction($this->getConnectionObject(), $action, $options);
        $this->expectException(TransactionException::class);
        $transaction->execute();
    }
}
