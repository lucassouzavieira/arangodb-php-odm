<?php

namespace Unit\Validation;

use Unit\TestCase;
use ArangoDB\Validation\Bind;
use ArangoDB\Exception\ValidationException;

class BindTest extends TestCase
{
    public function testSet()
    {
        $container = new Bind();
        $container->set("metal_band", "Anthrax");

        $this->assertEquals(1, count($container->all()), "Must be only one element in container");
        $this->assertArrayHasKey("metal_band", $container->all());
    }

    public function testGet()
    {
        $container = new Bind();
        $container->set("rock_band", "Queen");

        $this->assertEquals("Queen", $container->get("rock_band"), "Variable cannot be changed by container");
    }

    public function testCount()
    {
        $container = new Bind();
        $container->set("metal_band", "Metallica");
        $this->assertEquals(1, $container->count(), "Must be only one element in container");

        $container->set("rock_band", "The Beatles");
        $this->assertEquals(2, $container->count(), "Must be two elements in container");
    }

    public function testAll()
    {
        $container = new Bind();
        $container->set("metal_band", "Megadeath");
        $container->set("rock_band", "The Kinks");

        $expected = [
            "metal_band" => "Megadeath",
            "rock_band" => "The Kinks",
        ];

        $this->assertEquals($expected, $container->all());
        $this->assertEquals(2, $container->count(), "Must be two elements in container");
    }

    public function testNameAsObjectThrowValidationException()
    {
        $this->expectException(ValidationException::class);
        $container = new Bind();
        $object = new \stdClass();
        $container->set($object, "value");
    }

    public function testNameAsFloatThrowValidationException()
    {
        $this->expectException(ValidationException::class);
        $container = new Bind();
        $object = new \stdClass();
        $container->set(17.5, "value");
    }

    public function testNameAsBooleanThrowValidationException()
    {
        $this->expectException(ValidationException::class);
        $container = new Bind();
        $container->set(false, "value");
    }

    /**
     * @expectedException ValidationException;
     */
    public function testValueThrowValidationException()
    {
        $this->expectException(ValidationException::class);
        $container = new Bind();
        $object = new \stdClass();
        $container->set("variable", $object);
    }

}