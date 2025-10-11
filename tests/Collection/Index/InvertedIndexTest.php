<?php

namespace Unit\Collection\Index;

use Unit\TestCase;
use ArangoDB\Collection\Index\InvertedIndex;

class InvertedIndexTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testConstructor(): void
    {
        $index = new InvertedIndex(['my_idx_attribute']);
        $this->assertTrue($index->isNew());
        $this->assertEquals("inverted", $index->getType());
    }

    public function testToArray(): void
    {
        $index = new InvertedIndex(['my_idx_attribute'], ['unique' => false]);
        $this->assertArrayHasKey('unique', $index->toArray());
        $this->assertFalse($index->toArray()['unique']);
    }
}
