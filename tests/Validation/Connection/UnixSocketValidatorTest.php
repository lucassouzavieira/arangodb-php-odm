<?php

namespace Unit\Validation\Connection;

use Unit\TestCase;
use ArangoDB\Exception\ValidationException;
use ArangoDB\Validation\Connection\UnixSocketValidator;

class UnixSocketValidatorTest extends TestCase
{
    public function testSuccessValidation()
    {
        $this->assertTrue(UnixSocketValidator::validate("unix://172.16.0.1:8529"));
    }

    public function testFailValidation()
    {
        $this->assertFalse(UnixSocketValidator::validate("ssh://localhost:8529"));
    }

    public function testThrowExceptionValidation()
    {
        $object = new \stdClass();
        $this->expectException(ValidationException::class);
        $this->assertFalse(UnixSocketValidator::validate($object));
    }
}