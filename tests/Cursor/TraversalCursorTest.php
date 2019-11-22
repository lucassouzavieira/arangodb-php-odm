<?php

namespace Unit\Cursor;

use ArangoDB\Graph\Graph;
use ArangoDB\AQL\Statement;
use ArangoDB\Cursor\Cursor;
use ArangoDB\Document\Edge;
use GuzzleHttp\Psr7\Response;
use ArangoDB\Document\Document;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\Cursor\TraversalCursor;
use ArangoDB\Cursor\Exceptions\CursorException;

class TraversalCursorTest extends CursorTestCase
{
    public function mockGraph()
    {
        $db = $this->getConnectionObject()->getDatabase();

        // Create collections.
        $cities = $db->createCollection("cities");

        // Vertex documents
        $saoPaulo = new Document(['_key' => 'gru', 'name' => 'São Paulo'], $cities);
        $saoLuis = new Document(['_key' => 'slz', 'name' => 'São Luís'], $cities);
        $imperatriz = new Document(['_key' => 'itz', 'name' => 'Imperatriz'], $cities);
        $florianopolis = new Document(['_key' => 'fln', 'name' => 'Florianópolis'], $cities);
        $brasilia = new Document(['_key' => 'bsb', 'name' => 'Brasília'], $cities);
        $maceio = new Document(['_key' => 'mcz', 'name' => 'Maceió'], $cities);
        $salvador = new Document(['_key' => 'ssa', 'name' => 'Salvador'], $cities);
        $manaus = new Document(['_key' => 'mao', 'name' => 'Manaus'], $cities);

        $saoPaulo->save();
        $saoLuis->save();
        $imperatriz->save();
        $florianopolis->save();
        $brasilia->save();
        $maceio->save();
        $salvador->save();
        $manaus->save();

        $graphAttributes = [
            'edgeDefinitions' => [
                [
                    'collection' => 'flights',
                    'from' => [
                        'cities',
                    ],
                    'to' => [
                        'cities'
                    ]
                ]
            ]
        ];

        // Graph
        $graph = new Graph("traversal_test_graph", $graphAttributes, $this->getConnectionObject()->getDatabase());
        $graph->save();

        // Edges
        $flights = $db->getCollection('flights');

        // Graphs are always directed.

        // GRU flights
        $fl = new Edge(['_from' => 'cities/gru', '_to' => 'cities/slz'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/gru', '_to' => 'cities/itz'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/gru', '_to' => 'cities/bsb'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/gru', '_to' => 'cities/mcz'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/gru', '_to' => 'cities/fln'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/gru', '_to' => 'cities/ssa'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/gru', '_to' => 'cities/mao'], $flights);
        $fl->save();

        // SLZ flights
        $fl = new Edge(['_from' => 'cities/slz', '_to' => 'cities/bsb'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/slz', '_to' => 'cities/itz'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/slz', '_to' => 'cities/gru'], $flights);
        $fl->save();

        // ITZ flights
        $fl = new Edge(['_from' => 'cities/itz', '_to' => 'cities/bsb'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/itz', '_to' => 'cities/slz'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/itz', '_to' => 'cities/gru'], $flights);
        $fl->save();

        // FLN flights
        $fl = new Edge(['_from' => 'cities/fln', '_to' => 'cities/gru'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/fln', '_to' => 'cities/bsb'], $flights);
        $fl->save();

        // MCZ flights
        $fl = new Edge(['_from' => 'cities/mcz', '_to' => 'cities/gru'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/mcz', '_to' => 'cities/bsb'], $flights);
        $fl->save();

        // BSB flights
        $fl = new Edge(['_from' => 'cities/bsb', '_to' => 'cities/gru'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/bsb', '_to' => 'cities/slz'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/bsb', '_to' => 'cities/itz'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/bsb', '_to' => 'cities/mcz'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/bsb', '_to' => 'cities/fln'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/bsb', '_to' => 'cities/ssa'], $flights);
        $fl->save();
        $fl = new Edge(['_from' => 'cities/bsb', '_to' => 'cities/mao'], $flights);
        $fl->save();
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->mockGraph();
    }

    public function tearDown(): void
    {
        $db = $this->getConnectionObject()->getDatabase();
        $db->getGraph('traversal_test_graph')->delete(true);
        parent::tearDown();
    }

    public function testConstructorThrowCursorException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => []])),
            new Response(200, [], json_encode(['result' => [], 'name' => 'test_cursor_coll'])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $statement = "
            FOR v, e, p IN 1..@depth OUTBOUND
            @start
            GRAPH @graph
            RETURN p
        ";

        $statement = new Statement($statement);
        $statement->bindValue("@depth", 1);
        $statement->bindValue("@start", 'cities/itz');
        $statement->bindValue("@graph", 'traversal_test_graph');

        $connection = $this->getConnectionObject($mock);
        $connection->getDatabase()->createCollection('test_cursor_coll');

        $this->expectException(CursorException::class);
        $cursor = new TraversalCursor($connection, $statement);
    }
}
