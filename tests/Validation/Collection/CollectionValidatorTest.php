<?php


namespace Unit\Validation\Collection;

use Unit\TestCase;
use ArangoDB\Validation\Collection\CollectionValidator;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

class CollectionValidatorTest extends TestCase
{
    protected function mockCollectionArray()
    {
        $buckets = [2, 4, 8, 16, 32, 64, 128, 256, 512, 1024];
        return [
            'name' => random_bytes(10),
            'journalSize' => rand(1048576, 2097152),
            'replicationFactor' => rand(1, 10),
            'waitForSync' => (bool)rand(0, 1),
            'doCompact' => (bool)rand(0, 1),
            'shardingStrategy' => 'community-compat',
            'isVolatile' => (bool)rand(0, 1),
            'shardKeys' => ["_key"],
            'numberOfShards' => rand(1, 10),
            'isSystem' => (bool)rand(0, 1),
            'type' => rand(2, 3),
            'keyOptions' => [
                'allowUserKeys' => (bool)rand(0, 1),
                'type' => 'traditional',
                'lastValue' => 0
            ],
            'indexBuckets' => $buckets[rand(0, 9)]
        ];
    }

    public function testRules()
    {
        $collectionValidator = new CollectionValidator($this->mockCollectionArray());
        $this->assertIsArray($collectionValidator->rules());
    }

    public function testValidate()
    {
        $collectionValidator = new CollectionValidator($this->mockCollectionArray());
        $this->assertTrue($collectionValidator->validate());
    }

    public function testThrowMissingParameterExceptionForRequired()
    {
        $mock = $this->mockCollectionArray();
        unset($mock['name']);
        $collectionValidator = new CollectionValidator($mock);
        $this->expectException(MissingParameterException::class);
        $this->assertTrue($collectionValidator->validate());
    }

    public function testThrowInvalidParameterException()
    {
        $mock = $this->mockCollectionArray();
        $mock['numberOfShards'] = 'anystr';

        $collectionValidator = new CollectionValidator($mock);
        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($collectionValidator->validate());

        $mock = $this->mockCollectionArray();
        $mock['keyOptions'] = 10;

        $collectionValidator = new CollectionValidator($mock);
        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($collectionValidator->validate());

        $mock = $this->mockCollectionArray();
        $mock['indexBuckets'] = 48;

        $collectionValidator = new CollectionValidator($mock);
        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($collectionValidator->validate());

        $mock = $this->mockCollectionArray();
        $mock['waitForSync'] = "wait";

        $collectionValidator = new CollectionValidator($mock);
        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($collectionValidator->validate());
    }
}
