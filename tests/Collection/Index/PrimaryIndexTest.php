<?php


namespace Unit\Collection\GeneralIndex;

use ArangoDB\Exceptions\IndexException;
use Unit\TestCase;
use ArangoDB\Collection\Index\PrimaryIndex;

class PrimaryIndexTest extends TestCase
{
    public function testGetCreateData()
    {
        $index = new PrimaryIndex(['custom_field' => 'any']);
        $this->expectException(IndexException::class);
        $data = $index->getCreateData();
    }
}
