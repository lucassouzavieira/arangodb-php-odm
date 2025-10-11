<?php

namespace Unit\Collection\Index;

use ArangoDB\Collection\Index\InvertedIndex;
use Unit\TestCase;
use ArangoDB\Collection\Index\Factory;
use ArangoDB\Exceptions\IndexException;
use ArangoDB\Collection\Index\TTLIndex;
use ArangoDB\Collection\Index\HashIndex;
use ArangoDB\Collection\Index\EdgeIndex;
use ArangoDB\Collection\Index\PrimaryIndex;
use ArangoDB\Collection\Index\FullTextIndex;
use ArangoDB\Collection\Index\SkipListIndex;
use ArangoDB\Collection\Index\PersistentIndex;
use ArangoDB\Collection\Index\GeoSpatialIndex;
use ArangoDB\Validation\Exceptions\MissingParameterException;

class FactoryTest extends TestCase
{
    public function mockPrimaryArray(): array
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

    public function mockEdgeArray(): array
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

    public function mockHashArray(): array
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

    public function mockGeoSpatialArray(): array
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

    public function mockFullTextArray(): array
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

    public function mockSkipListArray(): array
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

    public function mockPersistentArray(): array
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

    public function mockTTLArray(): array
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

    public function mockGenericArray(): array
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

    public function mockInvertedArray(): array
    {
        return [
            'fields' => [
                'my_field',
            ],
            'id' => 'coll/0',
            'name' => 'inverted_idx',
            'sparse' => true,
            'unique' => false,
            'type' => 'inverted',
            'selectivityEstimate' => 0.015
        ];
    }

    public function testFactoryMakesPrimaryIndex(): void
    {
        $index = Factory::factory($this->mockPrimaryArray());
        $this->assertInstanceOf(PrimaryIndex::class, $index);
    }

    public function testFactoryMakesEdgeIndex(): void
    {
        $index = Factory::factory($this->mockEdgeArray());
        $this->assertInstanceOf(EdgeIndex::class, $index);
    }

    public function testFactoryMakesHashIndex(): void
    {
        $index = Factory::factory($this->mockHashArray());
        $this->assertInstanceOf(HashIndex::class, $index);
    }

    public function testFactoryMakesGeoSpatialIndex(): void
    {
        $index = Factory::factory($this->mockGeoSpatialArray());
        $this->assertInstanceOf(GeoSpatialIndex::class, $index);
    }

    public function testFactoryMakesFullTextIndex(): void
    {
        $index = Factory::factory($this->mockFullTextArray());
        $this->assertInstanceOf(FullTextIndex::class, $index);
    }

    public function testFactoryMakesSkipListIndex(): void
    {
        $index = Factory::factory($this->mockSkipListArray());
        $this->assertInstanceOf(SkipListIndex::class, $index);
    }

    public function testFactoryMakesPersistentIndex(): void
    {
        $index = Factory::factory($this->mockPersistentArray());
        $this->assertInstanceOf(PersistentIndex::class, $index);
    }

    public function testFactoryMakesTTLIndex(): void
    {
        $index = Factory::factory($this->mockTTLArray());
        $this->assertInstanceOf(TTLIndex::class, $index);
    }

    public function testFactoryMakesInvertedIndex(): void
    {
        $index = Factory::factory($this->mockInvertedArray());
        $this->assertInstanceOf(InvertedIndex::class, $index);
    }

    public function testFactoryMakesGenericIndex(): void
    {
        $this->expectException(IndexException::class);
        $index = Factory::factory($this->mockGenericArray());
    }

    public function testFactoryThrowMissingParameterException(): void
    {
        $attributes = $this->mockGenericArray();
        unset($attributes['type']);

        $this->expectException(MissingParameterException::class);
        Factory::factory($attributes);
    }
}
