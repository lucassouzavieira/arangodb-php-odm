<?php

namespace Unit\Collection\Index;

use Unit\TestCase;
use ArangoDB\Admin\Server;
use ArangoDB\Collection\Index\TTLIndex;

class TTLIndexTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testConstructor()
    {
        $index = new TTLIndex(['my_ttl_attr']);
        $this->assertTrue($index->isNew());
        $this->assertEquals("ttl", $index->getType());
    }

    public function testExpiresAfter()
    {
        $index = new TTLIndex(['my_ttl_attr'], 1800);
        $this->assertEquals(1800, $index->expireAfter());
    }

    public function testToArray()
    {
        $index = new TTLIndex(['my_ttl_attr'], false);
        $this->assertArrayHasKey('expireAfter', $index->toArray());
    }

    public function testGetCreateData()
    {
        $index = new TTLIndex(['my_ttl_attr']);
        $this->assertCount(3, $index->getCreateData());
        $data = $index->getCreateData();
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('fields', $data);
        $this->assertArrayHasKey('expireAfter', $data);
    }
}
