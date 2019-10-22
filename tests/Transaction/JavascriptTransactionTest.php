<?php


namespace Unit\Transaction;

use ArangoDB\Exceptions\TransactionException;
use ArangoDB\Transaction\JavascriptTransaction;
use Unit\TestCase;

class JavascriptTransactionTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
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
}
