<?php

namespace Unit\Validation\Connection;

use Unit\TestCase;
use ArangoDB\Exception\ValidationException;
use ArangoDB\Validation\Connection\SSLValidator;

class SSLValidatorTest extends TestCase
{
    public function testSuccessValidation()
    {
        $this->assertTrue(SSLValidator::validate("ssl://localhost:8529"));
        $this->assertTrue(SSLValidator::validate("https://localhost:8529"));
    }

    public function testFailValidation()
    {
        $this->assertFalse(SSLValidator::validate("http://localhost:8529"));
    }

    public function testThrowExceptionValidation()
    {
        $this->expectException(ValidationException::class);
        $this->assertFalse(SSLValidator::validate(15.5));
    }
}