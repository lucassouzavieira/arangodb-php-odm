<?php

namespace Unit\Collection\Index;

use Unit\TestCase;
use ArangoDB\Collection\Index\PersistentIndex;

class PersistentIndexTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testConstructor()
    {
        $index = new PersistentIndex(['my_persistent_attr']);

        $this->assertTrue($index->isNew());
        $this->assertTrue($index->isUnique());
        $this->assertEquals("persistent", $index->getType());
    }

    public function testGetCreateData()
    {
        $index = new PersistentIndex(['my_persistent_attr']);
        $this->assertCount(4, $index->getCreateData());
        $data = $index->getCreateData();
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('fields', $data);
        $this->assertArrayHasKey('unique', $data);
        $this->assertArrayHasKey('sparse', $data);
    }
}
