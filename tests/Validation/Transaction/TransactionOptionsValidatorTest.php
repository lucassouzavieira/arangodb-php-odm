<?php

namespace Unit\Validation\Transaction;

use Unit\TestCase;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Exceptions\TransactionException;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use ArangoDB\Validation\Transaction\TransactionOptionsValidator;

class TransactionOptionsValidatorTest extends TestCase
{
    public function getOptions()
    {
        return [
            'collections' => [
                'write' => [
                    'coll_a',
                    'coll_b',
                ],
                'read' => [
                    'coll_c',
                    'coll_d'
                ],
                'exclusive' => [
                    'coll_exclusive'
                ]
            ],
            'maxTransactionSize' => 32000000,
            'waitForSync' => true,
            'allowImplicit' => false,
            'lockTimeout' => 30,
        ];
    }

    public function testValidator()
    {
        $attributes = $this->getOptions();

        $validator = new TransactionOptionsValidator($attributes);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowMissingParameterExceptionOnMissingCollections()
    {
        $attributes = $this->getOptions();
        unset($attributes['collections']);

        $validator = new TransactionOptionsValidator($attributes);
        $this->expectException(MissingParameterException::class);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowTypeErrorExceptionOnNonArrayCollections()
    {
        $attributes = $this->getOptions();
        $attributes['collections'] = null;

        $validator = new TransactionOptionsValidator($attributes);
        $this->expectException(\TypeError::class);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowTransactionExceptionOnEmptyCollections()
    {
        $attributes = $this->getOptions();
        $attributes['collections'] = [];

        $validator = new TransactionOptionsValidator($attributes);
        $this->expectException(TransactionException::class);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowTransactionExceptionOnInvalidCollectionName()
    {
        $attributes = $this->getOptions();
        $attributes['collections'] = ['write' => [new ArrayList()]];

        $validator = new TransactionOptionsValidator($attributes);
        $this->expectException(TransactionException::class);
        $this->assertTrue($validator->validate());
    }

    public function testAcceptsOnlyWriteAttributeOnCollections()
    {
        $attributes = $this->getOptions();
        $attributes['collections'] = ['write' => ['coll_any']];

        $validator = new TransactionOptionsValidator($attributes);
        $this->assertTrue($validator->validate());
    }

    public function testAcceptsOnlyReadAttributeOnCollections()
    {
        $attributes = $this->getOptions();
        $attributes['collections'] = ['read' => ['coll_any']];

        $validator = new TransactionOptionsValidator($attributes);
        $this->assertTrue($validator->validate());
    }

    public function testAcceptsOnlyExclusiveAttributeOnCollections()
    {
        $attributes = $this->getOptions();
        $attributes['collections'] = ['exclusive' => ['coll_any']];

        $validator = new TransactionOptionsValidator($attributes);
        $this->assertTrue($validator->validate());
    }
}
