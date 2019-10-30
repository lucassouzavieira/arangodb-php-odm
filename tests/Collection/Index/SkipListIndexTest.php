<?php

namespace Unit\Collection\Index;

use Unit\TestCase;
use ArangoDB\Collection\Index\SkipListIndex;

class SkipListIndexTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testConstructor()
    {
        $index = new SkipListIndex(['my_skiplist_attr']);

        $this->assertTrue($index->isNew());
        $this->assertTrue($index->isDeduplicate());
        $this->assertEquals("skiplist", $index->getType());
    }

    public function testIsDeduplicate()
    {
        $index = new SkipListIndex(['my_skiplist_attr'], ['unique' => false, 'sparse' => false, 'deduplicate' => false]);
        $this->assertFalse($index->isUnique());
        $this->assertFalse($index->isSparse());
        $this->assertFalse($index->isDeduplicate());
    }

    public function testToArray()
    {
        $index = new SkipListIndex(['my_skiplist_attr', 'another_skiplist_attr']);
        $this->assertArrayHasKey('deduplicate', $index->toArray());
    }

    public function testGetCreateData()
    {
        $index = new SkipListIndex(['my_skiplist_attr']);
        $this->assertCount(5, $index->getCreateData());
        $data = $index->getCreateData();
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('fields', $data);
        $this->assertArrayHasKey('deduplicate', $data);
    }
}
