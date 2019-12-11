<?php

namespace Unit\Collection\Index;

use Unit\TestCase;
use ArangoDB\Collection\Index\GeoSpatialIndex;

class GeoSpatialIndexTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testConstructor()
    {
        $index = new GeoSpatialIndex(['my_geo_attr']);

        $this->assertTrue($index->isNew());
        $this->assertTrue($index->isGeoJson());
        $this->assertEquals("geo", $index->getType());
    }

    public function testGetGeoJSON()
    {
        $index = new GeoSpatialIndex(['my_geo_attr'], false);
        $this->assertFalse($index->isGeoJson());
    }

    public function testToArray()
    {
        $index = new GeoSpatialIndex(['my_geo_attr'], false);
        $this->assertArrayHasKey('geoJson', $index->toArray());
    }

    public function testGetCreateData()
    {
        $index = new GeoSpatialIndex(['my_geo_attr']);
        $this->assertCount(3, $index->getCreateData());
        $data = $index->getCreateData();
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('fields', $data);
        $this->assertArrayHasKey('geoJson', $data);
    }
}
