<?php


namespace Unit\Graph\Utils;

use ArangoDB\Graph\Graph;
use ArangoDB\Document\Edge;
use ArangoDB\Database\Database;
use ArangoDB\Document\Document;

trait MockGraphTrait
{
    public function mockGraph(Database $database)
    {
        // Create collections.
        $cities = $database->createCollection("cities");
        $flights = $database->createCollection("flights", ['type' => 3]);

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
        $graph = new Graph("traversal_test_graph", $graphAttributes, $database);
        $graph->save();

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
}
