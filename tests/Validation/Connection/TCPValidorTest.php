<?php

namespace Unit\Validation\Connection;

use Unit\TestCase;
use ArangoDB\Exception\ValidationException;
use ArangoDB\Validation\Connection\TCPValidator;

class TCPValidorTest extends TestCase
{
    public function testSuccessValidation()
    {
        $this->assertTrue(TCPValidator::validate("http://localhost:8529"));
        $this->assertTrue(TCPValidator::validate("tcp://localhost:8529"));
    }

    public function testFailValidation()
    {
        $this->assertFalse(TCPValidator::validate("ssh://localhost:8529"));
    }

    public function testThrowExceptionValidation()
    {
        $this->expectException(ValidationException::class);
        $this->assertFalse(TCPValidator::validate(0x900));
    }
}