<?php


namespace Unit\Collection\GeneralIndex;

use ArangoDB\Exceptions\IndexException;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use Unit\TestCase;
use ArangoDB\Collection\Index\Index;
use ArangoDB\Collection\Index\Factory;
use ArangoDB\Collection\Index\TTLIndex;
use ArangoDB\Collection\Index\HashIndex;
use ArangoDB\Collection\Index\EdgeIndex;
use ArangoDB\Collection\Index\PrimaryIndex;
use ArangoDB\Collection\Index\FullTextIndex;
use ArangoDB\Collection\Index\SkipListIndex;
use ArangoDB\Collection\Index\PersistentIndex;
use ArangoDB\Collection\Index\GeoSpatialIndex;

class FactoryTest extends TestCase
{
    public function mockPrimaryArray()
    {
        return [
            'fields' => [
                '_key',
                '_id'
            ],
            'id' => 'coll/0',
            'name' => 'primary',
            'sparse' => false,
            'unique' => true,
            'type' => 'primary',
            'selectivityEstimate' => 1
        ];
    }

    public function mockEdgeArray()
    {
        return [
            'fields' => [
                '_from',
                '_to'
            ],
            'id' => 'coll/0',
            'name' => 'primary',
            'sparse' => false,
            'unique' => false,
            'type' => 'edge',
            'selectivityEstimate' => 1
        ];
    }

    public function mockHashArray()
    {
        return [
            'fields' => [
                'my_field',
            ],
            'id' => 'coll/0',
            'name' => 'primary',
            'sparse' => true,
            'unique' => true,
            'deduplicate' => true,
            'type' => 'hash',
            'selectivityEstimate' => 1
        ];
    }

    public function mockGeoSpatialArray()
    {
        return [
            'fields' => [
                'my_field',
            ],
            'id' => 'coll/0',
            'name' => 'primary',
            'sparse' => true,
            'unique' => true,
            'deduplicate' => true,
            'geojson' => true,
            'type' => 'geo',
            'selectivityEstimate' => 0.018
        ];
    }

    public function mockFullTextArray()
    {
        return [
            'fields' => [
                'my_field',
            ],
            'id' => 'coll/0',
            'name' => 'primary',
            'sparse' => true,
            'unique' => false,
            'minLength' => 2,
            'type' => 'fulltext',
            'selectivityEstimate' => 0.018
        ];
    }

    public function mockSkipListArray()
    {
        return [
            'fields' => [
                'my_field',
            ],
            'id' => 'coll/0',
            'name' => 'primary',
            'sparse' => true,
            'unique' => false,
            'deduplicate' => true,
            'type' => 'skiplist',
            'selectivityEstimate' => 0.016
        ];
    }

    public function mockPersistentArray()
    {
        return [
            'fields' => [
                'my_field',
            ],
            'id' => 'coll/0',
            'name' => 'primary',
            'sparse' => true,
            'unique' => false,
            'type' => 'persistent',
            'selectivityEstimate' => 0.014
        ];
    }

    public function mockTTLArray()
    {
        return [
            'fields' => [
                'my_field',
            ],
            'id' => 'coll/0',
            'name' => 'primary',
            'sparse' => true,
            'unique' => false,
            'expireAfter' => 45,
            'type' => 'ttl',
            'selectivityEstimate' => 0.014
        ];
    }

    public function mockGenericArray()
    {
        return [
            'fields' => [
                'my_field',
            ],
            'id' => 'coll/0',
            'name' => 'primary',
            'sparse' => true,
            'unique' => false,
            'type' => 'generic',
            'selectivityEstimate' => 0.011
        ];
    }

    public function testFactoryMakesPrimaryIndex()
    {
        $index = Factory::factory($this->mockPrimaryArray());
        $this->assertInstanceOf(PrimaryIndex::class, $index);
    }

    public function testFactoryMakesEdgeIndex()
    {
        $index = Factory::factory($this->mockEdgeArray());
        $this->assertInstanceOf(EdgeIndex::class, $index);
    }

    public function testFactoryMakesHashIndex()
    {
        $index = Factory::factory($this->mockHashArray());
        $this->assertInstanceOf(HashIndex::class, $index);
    }

    public function testFactoryMakesGeoSpatialIndex()
    {
        $index = Factory::factory($this->mockGeoSpatialArray());
        $this->assertInstanceOf(GeoSpatialIndex::class, $index);
    }

    public function testFactoryMakesFullTextIndex()
    {
        $index = Factory::factory($this->mockFullTextArray());
        $this->assertInstanceOf(FullTextIndex::class, $index);
    }

    public function testFactoryMakesSkipListIndex()
    {
        $index = Factory::factory($this->mockSkipListArray());
        $this->assertInstanceOf(SkipListIndex::class, $index);
    }

    public function testFactoryMakesPersistentIndex()
    {
        $index = Factory::factory($this->mockPersistentArray());
        $this->assertInstanceOf(PersistentIndex::class, $index);
    }

    public function testFactoryMakesTTLIndex()
    {
        $index = Factory::factory($this->mockTTLArray());
        $this->assertInstanceOf(TTLIndex::class, $index);
    }

    public function testFactoryMakesGenericIndex()
    {
        $this->expectException(IndexException::class);
        $index = Factory::factory($this->mockGenericArray());
    }

    public function testFactoryThrowMissingParameterException()
    {
        $attributes = $this->mockGenericArray();
        unset($attributes['type']);

        $this->expectException(MissingParameterException::class);
        $index = Factory::factory($attributes);
    }
}
