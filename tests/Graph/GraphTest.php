<?php


namespace Unit\Graph;

use ArangoDB\Graph\EdgeDefinition;
use Unit\TestCase;
use ArangoDB\Graph\Graph;
use ArangoDB\DataStructures\ArrayList;

class GraphTest extends TestCase
{
    public function mockGraphAttributes($withDescriptors = false)
    {
        $descriptors = [
            '_id' => '_graphs/mygraph',
            '_key' => 'mygraph',
            '_rev' => '--zGahsoet1'
        ];

        $attributes = [
            'numberOfShards' => 1,
            'replicationFactor' => 1,
            'minReplicationFactor' => 1,
            'isSmart' => false,
            'edgeDefinitions' => [
                [
                    'collection' => 'someEdgeColl',
                    'from' => [
                        'coll_a',
                    ],
                    'to' => [
                        'coll_b'
                    ]
                ]
            ],
            'orphanCollections' => []
        ];

        if ($withDescriptors) {
            return array_merge($descriptors, $attributes);
        }

        return $attributes;
    }

    public function testConstructorOnlyWithName()
    {
        $graph = new Graph('my_graph');
        $this->assertTrue($graph->isNew());
        $this->assertFalse($graph->isSmart());
        $this->assertEquals('my_graph', $graph->getName());
        $this->assertEquals('my_graph', $graph->getKey());
        $this->assertEquals('', $graph->getId());
        $this->assertInstanceOf(ArrayList::class, $graph->getEdgeDefinitions());
        $this->assertCount(0, $graph->getEdgeDefinitions());
    }

    public function testConstructorWithAttributesWithoutDescriptors()
    {
        // Without descriptors
        $graph = new Graph('my_graph', $this->mockGraphAttributes());
        $this->assertTrue($graph->isNew());
        $this->assertFalse($graph->isSmart());
        $this->assertEquals('my_graph', $graph->getName());
        $this->assertEquals('my_graph', $graph->getKey());
        $this->assertEquals('', $graph->getId());
        $this->assertInstanceOf(ArrayList::class, $graph->getEdgeDefinitions());
        $this->assertCount(1, $graph->getEdgeDefinitions());
    }

    public function testConstructorWithAttributesWithDescriptors()
    {
        // With descriptors
        $graph = new Graph('my_graph', $this->mockGraphAttributes(true));
        $this->assertFalse($graph->isNew());
        $this->assertFalse($graph->isSmart());
        $this->assertEquals('my_graph', $graph->getName());
        $this->assertEquals('my_graph', $graph->getKey());
        $this->assertEquals('_graphs/mygraph', $graph->getId());
        $this->assertInstanceOf(ArrayList::class, $graph->getEdgeDefinitions());
        $this->assertCount(1, $graph->getEdgeDefinitions());
    }

    public function testConstructorWithAttributesWithArrayListForEdgesDefinitions()
    {
        // With descriptors
        $attributes = $this->mockGraphAttributes(true);
        $attributes['edgeDefinitions'] = new ArrayList([new EdgeDefinition('someEdgeColl', ['coll_a'], ['coll_b'])]);

        $graph = new Graph('my_graph', $attributes);
        $this->assertFalse($graph->isNew());
        $this->assertFalse($graph->isSmart());
        $this->assertEquals('my_graph', $graph->getName());
        $this->assertEquals('my_graph', $graph->getKey());
        $this->assertEquals('_graphs/mygraph', $graph->getId());
        $this->assertInstanceOf(ArrayList::class, $graph->getEdgeDefinitions());
        $this->assertCount(1, $graph->getEdgeDefinitions());
    }

    public function testIsNew()
    {
        $graph = new Graph('mygraph');
        $this->assertTrue($graph->isNew());

        $graph = new Graph('mygraph', $this->mockGraphAttributes(true));
        $this->assertFalse($graph->isNew());
    }

    public function testIsSmart()
    {
        $graph = new Graph('mygraph');
        $this->assertFalse($graph->isSmart());

        $attributes = $this->mockGraphAttributes(true);
        $attributes['isSmart'] = true;

        $graph = new Graph('mygraph', $attributes);
        $this->assertTrue($graph->isSmart());
    }

    public function testGetRevision()
    {
        $graph = new Graph('mygraph');
        $this->assertEquals(0, strlen($graph->getRevision()));

        $attributes = $this->mockGraphAttributes(true);
        $graph = new Graph('mygraph', $attributes);
        $this->assertGreaterThan(0, strlen($graph->getRevision()));
    }

    public function testGetNumberOfShards()
    {
        $graph = new Graph('mygraph');
        $this->assertEquals(1, $graph->getNumberOfShards());

        $attributes = $this->mockGraphAttributes(true);
        $attributes['numberOfShards'] = 2;

        $graph = new Graph('mygraph', $attributes);
        $this->assertEquals(2, $graph->getNumberOfShards());
    }

    public function testGetReplicationFactor()
    {
        $graph = new Graph('mygraph');
        $this->assertEquals(1, $graph->getReplicationFactor());

        $attributes = $this->mockGraphAttributes(true);
        $attributes['replicationFactor'] = 2;

        $graph = new Graph('mygraph', $attributes);
        $this->assertEquals(2, $graph->getReplicationFactor());
    }

    public function testGetMinReplicationFactor()
    {
        $graph = new Graph('mygraph');
        $this->assertEquals(1, $graph->getMinReplicationFactor());

        $attributes = $this->mockGraphAttributes(true);
        $attributes['minReplicationFactor'] = 2;

        $graph = new Graph('mygraph', $attributes);
        $this->assertEquals(2, $graph->getMinReplicationFactor());
    }

    public function testGetOrphanCollections()
    {
        $graph = new Graph('mygraph');
        $this->assertIsArray($graph->getOrphanCollections());
        $this->assertCount(0, $graph->getOrphanCollections());

        $attributes = $this->mockGraphAttributes(true);
        $attributes['orphanCollections'] = ['orphan'];

        $graph = new Graph('mygraph', $attributes);
        $this->assertIsArray($graph->getOrphanCollections());
        $this->assertCount(1, $graph->getOrphanCollections());
        $this->assertTrue(in_array('orphan', $graph->getOrphanCollections()));
    }
}
