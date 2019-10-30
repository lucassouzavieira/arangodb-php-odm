<?php


namespace Unit\Collection\GeneralIndex;

use Unit\TestCase;
use ArangoDB\Collection\Index\EdgeIndex;
use ArangoDB\Exceptions\IndexException;

class EdgeIndexTest extends TestCase
{
    public function testGetCreateData()
    {
        $index = new EdgeIndex(['custom_field' => 'any']);
        $this->expectException(IndexException::class);
        $data = $index->getCreateData();
    }

    public function testGetType()
    {
        $index = new EdgeIndex(['custom_field' => 'any']);
        $this->assertEquals('edge', $index->getType());
    }
}
