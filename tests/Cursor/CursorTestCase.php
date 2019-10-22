<?php

namespace Unit\Cursor;

use Unit\TestCase;
use ArangoDB\Document\Document;

class CursorTestCase extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->getConnectionObject()->getDatabase()->dropCollection('test_cursor_coll');
        parent::tearDown();
    }

    public function getCollection($quantity = 500)
    {
        $db = $this->getConnectionObject()->getDatabase();

        if (!$db->hasCollection('test_cursor_coll')) {
            $db->createCollection('test_cursor_coll');
        }

        $collection = $db->getCollection('test_cursor_coll');
        $planets = ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupyter', 'Saturn', 'Uranus', 'Neptune'];

        // Create 1000 documents
        for ($i = 0; $i < $quantity; $i++) {
            $document = new Document(['hello' => $planets[rand(0, 7)]], $collection);
            $document->save();
        }

        return $collection;
    }

    public function getMockArray($quantity = 500)
    {
        $results = [];
        $planets = ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupyter', 'Saturn', 'Uranus', 'Neptune'];
        // Create 1000 documents
        for ($i = 0; $i < $quantity; $i++) {
            $results[] = ['hello' => $planets[rand(0, 7)]];
        }

        return $results;
    }
}
