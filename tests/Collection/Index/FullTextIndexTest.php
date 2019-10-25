<?php

namespace Unit\Collection\Index;

use Unit\TestCase;
use ArangoDB\Collection\Index\FullTextIndex;

class FullTextIndexTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->getConnectionObject()->getDatabase()->dropCollection('fulltext_coll');
        parent::tearDown();
    }

    public function testConstructor()
    {
        $index = new FullTextIndex(['my_text_attr'], 3);

        $this->assertTrue($index->isNew());
        $this->assertEquals("fulltext", $index->getType());
    }

    public function testGetMinLength()
    {
        $index = new FullTextIndex(['my_text_attr'], 3);

        $this->assertEquals(3, $index->getMinLength());
    }

    public function testGetCreateData()
    {
        $index = new FullTextIndex(['my_text_attr']);
        $this->assertCount(2, $index->getCreateData());
        $this->assertArrayNotHasKey('minLength', $index->getCreateData());

        $index = new FullTextIndex(['my_text_attr'], 2);
        $this->assertCount(3, $index->getCreateData());
        $this->assertArrayHasKey('minLength', $index->getCreateData());
    }
}
