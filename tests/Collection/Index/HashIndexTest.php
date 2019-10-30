<?php

namespace Unit\Collection\Index;

use Unit\TestCase;
use ArangoDB\Collection\Index\HashIndex;

class HashIndexTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testConstructor()
    {
        $index = new HashIndex(['my_hash_attr']);

        $this->assertTrue($index->isNew());
        $this->assertTrue($index->isDeduplicate());
        $this->assertEquals("hash", $index->getType());
    }

    public function testIsDeduplicate()
    {
        $index = new HashIndex(['my_hash_attr'], ['unique' => false, 'sparse' => false, 'deduplicate' => false]);
        $this->assertFalse($index->isUnique());
        $this->assertFalse($index->isSparse());
        $this->assertFalse($index->isDeduplicate());
    }

    public function testToArray()
    {
        $index = new HashIndex(['my_hash_attr']);
        $this->assertArrayHasKey('deduplicate', $index->toArray());
    }

    public function testGetCreateData()
    {
        $index = new HashIndex(['my_hash_attr']);
        $this->assertCount(5, $index->getCreateData());
        $data = $index->getCreateData();
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('fields', $data);
        $this->assertArrayHasKey('deduplicate', $data);
    }
}
