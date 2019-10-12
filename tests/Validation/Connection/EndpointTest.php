<?php

namespace Unit\Validation\Connection;

use Unit\TestCase;
use ArangoDB\Exception\ValidationException;
use ArangoDB\Validation\Connection\Endpoint;

class EndpointTest extends TestCase
{
    public function testSuccessValidation()
    {
        // TCP endpoints
        $this->assertTrue(Endpoint::validate("http://localhost:8529"));
        $this->assertTrue(Endpoint::validate("tcp://localhost:8529"));

        // SSL endpoints
        $this->assertTrue(Endpoint::validate("ssl://localhost:8529"));
        $this->assertTrue(Endpoint::validate("https://localhost:8529"));

        // Unix sockets
        $this->assertTrue(Endpoint::validate("unix://172.16.0.1:8529"));
    }
}