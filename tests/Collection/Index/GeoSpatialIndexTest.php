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

    public function tearDown(): void
    {
        $this->getConnectionObject()->getDatabase()->dropCollection('geospatial_coll');
        parent::tearDown();
    }

    public function testConstructor()
    {
        $index = new GeoSpatialIndex(['my_geo_attr']);

        $this->assertTrue($index->isNew());
        $this->assertTrue($index->getGeoJson());
        $this->assertEquals("geo", $index->getType());
    }

    public function testGetGeoJSON()
    {
        $index = new GeoSpatialIndex(['my_geo_attr'], false);
        $this->assertFalse($index->getGeoJson());
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
