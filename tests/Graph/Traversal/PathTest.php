<?php


namespace Unit\Graph\Traversal;

use ArangoDB\Document\Edge;
use ArangoDB\Document\Vertex;
use Unit\Graph\BaseGraphTest;
use ArangoDB\Graph\Traversal\Path;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

class PathTest extends BaseGraphTest
{
    protected function mockData()
    {
        return [
            'edges' => [
                [
                    '_id' => 'edge/e1',
                    '_rev' => '--zGsdfa-_a',
                    '_key' => 'e1',
                    '_from' => 'coll_a/a1m',
                    '_to' => 'coll_a/a2m'
                ],
                [
                    '_id' => 'edge/e2',
                    '_rev' => '--zedFfa-_a',
                    '_key' => 'e2',
                    '_from' => 'coll_a/a2m',
                    '_to' => 'coll_a/a3m'
                ]
            ],
            'vertices' => [
                [
                    '_id' => 'coll_a/a1m',
                    '_rev' => '--zGedfa-_a',
                    '_key' => 'a1m',
                ],
                [
                    '_id' => 'coll_a/a2m',
                    '_rev' => '--zGhjfa-_a',
                    '_key' => 'a2m',
                ],
                [
                    '_id' => 'coll_a/a3m',
                    '_rev' => '--Dssdfa-_a',
                    '_key' => 'a3m',
                ]
            ]
        ];
    }

    public function testConstruct()
    {
        $data = $this->mockData();
        $path = new Path($data['edges'], $data['vertices']);

        $this->assertInstanceOf(ArrayList::class, $path->getEdges());
        $this->assertInstanceOf(ArrayList::class, $path->getVertices());

        $this->assertCount(2, $path->getEdges());
        $this->assertCount(3, $path->getVertices());
    }

    public function testConstructThrowMissingParameterException()
    {
        $data = $this->mockData();
        $edges = $data['edges'];
        $vertices = $data['vertices'];

        unset($edges[0]['_from']);

        $this->expectException(MissingParameterException::class);
        $path = new Path($edges, $vertices);
    }

    public function testConstructThrowInvalidParameterException()
    {
        $data = $this->mockData();
        $edges = $data['edges'];
        $vertices = $data['vertices'];

        $edges[0]['_to'] = new ArrayList();

        $this->expectException(InvalidParameterException::class);
        $path = new Path($edges, $vertices);
    }

    public function testGetEdges()
    {
        $data = $this->mockData();
        $edges = $data['edges'];
        $vertices = $data['vertices'];

        $path = new Path($edges, $vertices);

        $this->assertInstanceOf(ArrayList::class, $path->getEdges());
        $this->assertInstanceOf(Edge::class, $path->getEdges()->first());
        $this->assertCount(2, $path->getEdges());
    }

    public function testGetVertices()
    {
        $data = $this->mockData();
        $edges = $data['edges'];
        $vertices = $data['vertices'];

        $path = new Path($edges, $vertices);

        $this->assertInstanceOf(ArrayList::class, $path->getVertices());
        $this->assertInstanceOf(Vertex::class, $path->getVertices()->first());
        $this->assertCount(3, $path->getVertices());
    }
}
