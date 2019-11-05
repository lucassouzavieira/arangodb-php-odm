<?php


namespace Unit\Validation\Graph;

use Unit\TestCase;
use ArangoDB\Exceptions\Exception;
use ArangoDB\Validation\Graph\GraphValidator;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

class GraphValidatorTest extends TestCase
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

    public function testValidator()
    {
        // Without descriptors
        $attributes = $this->mockGraphAttributes();
        $validator = new GraphValidator($attributes);
        $this->assertTrue($validator->validate());

        // Without descriptors
        $attributes = $this->mockGraphAttributes(true);
        $validator = new GraphValidator($attributes);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowMissingParameterException()
    {
        // Without descriptors
        $attributes = $this->mockGraphAttributes();
        unset($attributes['edgeDefinitions']);
        $validator = new GraphValidator($attributes);
        $this->expectException(MissingParameterException::class);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowInvalidParameterException()
    {
        // Without descriptors
        $attributes = $this->mockGraphAttributes();
        $attributes['isSmart'] = 'string';

        $validator = new GraphValidator($attributes);
        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowExceptionMissingCollectionParamOnSomeEdgeDefinition()
    {
        $edgeDefinitions = [
            [
                'collection' => 'someEdgeColl',
                'from' => [
                    'coll_a',
                ],
                'to' => [
                    'coll_b'
                ]
            ],
            [
                'from' => [
                    'coll_a',
                ],
                'to' => [
                    'coll_b'
                ]
            ]
        ];

        // Without descriptors
        $attributes = $this->mockGraphAttributes();
        $attributes['edgeDefinitions'] = $edgeDefinitions;

        $validator = new GraphValidator($attributes);
        $this->expectException(Exception::class);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowExceptionMissingFromParamOnSomeEdgeDefinition()
    {
        $edgeDefinitions = [
            [
                'collection' => 'someEdgeColl',
                'from' => [
                    'coll_a',
                ],
                'to' => [
                    'coll_b'
                ]
            ],
            [
                'collection' => 'someEdgeColl',
                'to' => [
                    'coll_b'
                ]
            ]
        ];

        // Without descriptors
        $attributes = $this->mockGraphAttributes();
        $attributes['edgeDefinitions'] = $edgeDefinitions;

        $validator = new GraphValidator($attributes);
        $this->expectException(Exception::class);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowExceptionMissingToParamOnSomeEdgeDefinition()
    {
        $edgeDefinitions = [
            [
                'collection' => 'someEdgeColl',
                'from' => [
                    'coll_a',
                ],
                'to' => [
                    'coll_b'
                ]
            ],
            [
                'collection' => 'someEdgeColl',
                'from' => [
                    'coll_a',
                ]
            ]
        ];

        // Without descriptors
        $attributes = $this->mockGraphAttributes();
        $attributes['edgeDefinitions'] = $edgeDefinitions;

        $validator = new GraphValidator($attributes);
        $this->expectException(Exception::class);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowInvalidParamExceptionOnCollectionParamOnSomeEdgeDefinition()
    {
        $edgeDefinitions = [
            [
                'collection' => 45,
                'from' => [
                    'coll_a',
                ],
                'to' => [
                    'coll_b'
                ]
            ],
            [
                'collection' => 'someEdgeColl',
                'from' => [
                    'coll_a',
                ],
                'to' => [
                    'coll_b'
                ]
            ]
        ];

        // Without descriptors
        $attributes = $this->mockGraphAttributes();
        $attributes['edgeDefinitions'] = $edgeDefinitions;

        $validator = new GraphValidator($attributes);
        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowInvalidParamExceptionOnToParamOnSomeEdgeDefinition()
    {
        $edgeDefinitions = [
            [
                'collection' => 'someEdgeColl',
                'from' => [
                    'coll_a',
                ],
                'to' => [
                    'coll_b'
                ]
            ],
            [
                'collection' => 'someEdgeColl',
                'from' => [
                    'coll_a',
                ],
                'to' => false
            ]
        ];

        // Without descriptors
        $attributes = $this->mockGraphAttributes();
        $attributes['edgeDefinitions'] = $edgeDefinitions;

        $validator = new GraphValidator($attributes);
        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowInvalidParamExceptionOnFromParamOnSomeEdgeDefinition()
    {
        $edgeDefinitions = [
            [
                'collection' => 'someEdgeColl',
                'from' => [
                    'coll_a',
                ],
                'to' => [
                    'coll_b'
                ]
            ],
            [
                'collection' => 'someEdgeColl',
                'from' => 'hi',
                'to' => [
                    'coll_b'
                ]
            ]
        ];

        // Without descriptors
        $attributes = $this->mockGraphAttributes();
        $attributes['edgeDefinitions'] = $edgeDefinitions;

        $validator = new GraphValidator($attributes);
        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($validator->validate());
    }
}
