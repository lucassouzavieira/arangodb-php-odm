<?php

namespace Unit\Validation;

use Unit\TestCase;
use ArangoDB\Validation\Validator;
use ArangoDB\Exception\ValidationException;

/**
 * Class ValidatorTest
 *
 * @package Unit\Validation
 */
class ValidatorTest extends TestCase
{
    public function testValidateInteger()
    {
        $this->assertTrue(Validator::validate(random_int(10, 100)), "Integer values must pass");
    }

    public function testValidateString()
    {
        $this->assertTrue(Validator::validate(random_bytes(10)), "String values must pass");
    }

    public function testValidateFloat()
    {
        $float = random_int(10, 100) + 0.4;
        $this->assertTrue(Validator::validate($float), "Integer values must pass");
    }

    public function testValidateNull()
    {
        $this->assertTrue(Validator::validate(null), "Integer values must pass");
    }

    public function testValidateBoolean()
    {
        $this->assertTrue(Validator::validate(true), "Integer values must pass");
        $this->assertTrue(Validator::validate(false), "Integer values must pass");
    }

    public function testValidateArray()
    {
        $value = [
            'integer' => random_int(10, 100),
            'float' => random_int(10, 100) + 0.4,
            'bool' => (bool)rand(0, 1),
            'nullable' => null,
            'inner_array' => [
                'integer' => random_int(10, 100),
                'float' => random_int(10, 100) + 0.4,
                'bool' => (bool)rand(0, 1),
                'nullable' => null,
            ]
        ];

        $this->assertTrue(Validator::validate($value), "Integer values must pass");
    }

    public function testThrowValidationException()
    {
        $this->expectException(ValidationException::class);
        $object = new \stdClass();
        $this->assertTrue(Validator::validate($object), "Object values must not pass");
    }
}