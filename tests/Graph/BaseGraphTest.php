<?php


namespace Unit\Graph;

use Unit\TestCase;
use Unit\Graph\Utils\MockGraphTrait;

abstract class BaseGraphTest extends TestCase
{
    use MockGraphTrait;

    public function setUp(): void
    {
        $this->loadEnvironment();
        $this->mockGraph($this->getConnectionObject()->getDatabase());
        parent::setUp();
    }

    public function tearDown(): void
    {
        $db = $this->getConnectionObject()->getDatabase();
        $db->dropCollection('coll_a');
        $db->dropCollection('coll_b');
        $db->dropCollection('edge_coll');
        $db->dropCollection('edge_coll_b');

        if ($db->getGraph("traversal_test_graph")) {
            $db->getGraph('traversal_test_graph')->delete(true);
        }

        parent::tearDown();
    }

    public function mockEdgeDefinitions()
    {
        return [
            'collection' => 'edge_coll',
            'from' => [
                'coll_a'
            ],
            'to' => [
                'coll_b'
            ]
        ];
    }

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
}
