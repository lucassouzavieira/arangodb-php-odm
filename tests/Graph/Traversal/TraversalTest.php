<?php


namespace Unit\Graph\Traversal;

use ArangoDB\Document\Vertex;
use Unit\Graph\BaseGraphTest;
use ArangoDB\Exceptions\Exception;
use ArangoDB\Graph\Traversal\Traversal;

class TraversalTest extends BaseGraphTest
{
    public function testTraversalQuery()
    {
        $cities = $this->getConnectionObject()->getDatabase()->getCollection('cities');
        $vertex = $cities->findByKey("itz", true);

        $traversal = Traversal::traversalQuery($vertex, "traversal_test_graph", Traversal::GRAPH_DIRECTION_OUTBOUND, 2);

        $this->assertStringContainsString("cities/itz", $traversal->toAql());
        $this->assertStringContainsString("traversal_test_graph", $traversal->toAql());
        $this->assertStringContainsString("OUTBOUND", $traversal->toAql());
        $this->assertStringContainsString("2", $traversal->toAql());

        $this->assertInstanceOf(Traversal::class, $traversal);
    }

    public function testTraversalQueryThrowException()
    {
        $cities = $this->getConnectionObject()->getDatabase()->getCollection('cities');
        $document = $cities->findByKey("itz", true);
        $vertex = new Vertex($document->toArray());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The given Vertex object hasn't a Connection set.");
        $traversal = Traversal::traversalQuery($vertex, "traversal_test_graph", Traversal::GRAPH_DIRECTION_OUTBOUND, 2);
    }
}
