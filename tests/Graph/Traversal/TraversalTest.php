<?php


namespace Unit\Graph\Traversal;

use ArangoDB\Document\Vertex;
use ArangoDB\Graph\Traversal\Traversal;
use Unit\Graph\BaseGraphTest;

class TraversalTest extends BaseGraphTest
{
    protected function mockVertex()
    {
        return [
            '_id' => 'coll_a/a1m',
            '_rev' => '--zGedfa-_a',
            '_key' => 'a1m',
        ];
    }

    public function testTraversalQuery()
    {
        $vertex = new Vertex($this->mockVertex());
        $traversal = Traversal::traversalQuery($vertex, "my_graph", Traversal::GRAPH_DIRECTION_OUTBOUND, 2);

        $this->assertStringContainsString("coll_a/a1m", $traversal->toAql());
        $this->assertStringContainsString("my_graph", $traversal->toAql());
        $this->assertStringContainsString("OUTBOUND", $traversal->toAql());
        $this->assertStringContainsString("2", $traversal->toAql());

        $this->assertInstanceOf(Traversal::class, $traversal);
    }
}
