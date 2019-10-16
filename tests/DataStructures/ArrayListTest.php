<?php
declare(strict_types=1);

namespace Unit\DataStructures;

use Unit\TestCase;
use ArangoDB\DataStructures\ArrayList;

class ArrayListTest extends TestCase
{
    public function testFirst()
    {
        // Empty list
        $list = new ArrayList();
        $this->assertNull($list->first());

        $list = new ArrayList([
            'uni', 'dos', 'tres'
        ]);

        $this->assertEquals('uni', $list->first());
    }

    public function testLast()
    {
        // Empty list
        $list = new ArrayList();
        $this->assertNull($list->last());

        $list = new ArrayList([
            'uni', 'dos', 'tres'
        ]);

        $this->assertEquals('tres', $list->last());
    }

    public function testShouldReturnNullForNonExistingKey()
    {
        $list = new ArrayList();
        $this->assertNull($list->get('b'));
    }

    public function testShouldReturnDataForExistingKey()
    {
        $list = new ArrayList([
            'a' => 'Moon', 'b' => 'Jupyter', 10 => new \stdClass()
        ]);

        $this->assertEquals('Moon', $list->get('a'));
        $this->assertEquals('Jupyter', $list->get('b'));
        $this->assertInstanceOf(\stdClass::class, $list->get(10));
    }

    public function testPush()
    {
        $list = new ArrayList();
        $this->assertEquals(0, $list->count());

        $list->push('New piece of data');
        $this->assertEquals(1, $list->count());
        $this->assertEquals('New piece of data', $list->get(0));
    }

    public function testPut()
    {
        $list = new ArrayList();
        $this->assertEquals(0, $list->count());

        $list->put('custom', 'New piece of data');
        $this->assertEquals(1, $list->count());
        $this->assertEquals('New piece of data', $list->get('custom'));
    }

    public function testHas()
    {
        $list = new ArrayList();
        $this->assertEquals(0, $list->count());
        $this->assertFalse($list->has(0));
        $this->assertFalse($list->has('any'));

        $list->put('customKey', 'New piece of data');
        $this->assertTrue($list->has('customKey'));
    }

    public function testRemove()
    {
        $list = new ArrayList([
            'a' => 'Moon', 'b' => 'Jupyter', 10 => new \stdClass()
        ]);
        $this->assertCount(3, $list);

        $list->remove(10);
        $this->assertFalse($list->has(10));
        $this->assertCount(2, $list);

        $list->remove('a');
        $this->assertFalse($list->has('a'));
        $this->assertCount(1, $list);

        $this->assertTrue($list->has('b'));
    }

    public function testToArray()
    {
        $list = new ArrayList([
            's' => 'Sun', 'm' => 'Mars', 10 => new \stdClass()
        ]);

        $this->assertCount(3, $list->toArray());
        $this->assertIsArray($list->toArray());
    }

    public function testJsonSerializable()
    {
        $list = new ArrayList([
            'm' => 'Mercury', 's' => 'Saturn', 15 => new \stdClass()
        ]);

        $json = json_encode($list);
        $this->assertJson($json);
    }

    public function testIterable()
    {
        $list = new ArrayList([
            'm' => 'Mercury', 's' => 'Saturn', 15 => new \stdClass()
        ]);

        $this->assertIsIterable($list);
    }

    public function testCurrent()
    {
        $list = new ArrayList([
            'Quiet Riot', 'Metallica', 'Slayer', 'Anthrax'
        ]);

        $current = $list->current();
        $this->assertEquals('Quiet Riot', $current);
        $this->assertEquals(0, $list->key());
    }

    public function testNext()
    {
        $list = new ArrayList([
            'Quiet Riot', 'Metallica', 'Slayer', 'Anthrax'
        ]);
        $list->next();

        $this->assertEquals('Metallica', $list->current());
        $this->assertEquals(1, $list->key());
    }

    public function testKey()
    {
        $list = new ArrayList([
            'Quiet Riot', 'Metallica', 'Slayer', 'Anthrax'
        ]);
        $list->next();
        $list->next();

        $this->assertEquals(2, $list->key());
    }

    public function testValidIfKeyIsValid()
    {
        $list = new ArrayList([
            'Quiet Riot', 'Metallica', 'Slayer', 'Anthrax'
        ]);

        $list->next();
        $list->next();

        $this->assertEquals(true, $list->valid());
    }

    public function testValidIfKeyIsInvalid()
    {
        $list = new ArrayList([
            'Quiet Riot', 'Metallica', 'Slayer', 'Anthrax'
        ]);

        $list->next();
        $list->next();
        $list->next();
        $list->next();

        $this->assertEquals(false, $list->valid());
    }

    public function testRewind()
    {
        $list = new ArrayList([
            'Quiet Riot', 'Metallica', 'Slayer', 'Anthrax'
        ]);

        $list->next();
        $list->next();
        $list->next();
        $this->assertEquals(3, $list->key());

        $list->rewind();
        $this->assertEquals(0, $list->key());
    }
}
