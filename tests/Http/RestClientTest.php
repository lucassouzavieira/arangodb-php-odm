<?php

namespace Unit\Http;

use Unit\TestCase;
use ArangoDB\Http\RestClient;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

class RestClientTest extends TestCase
{
    public function testThrowInvalidParameterException()
    {
        $invalidUri = "any.thing.com";
        $this->expectException(InvalidParameterException::class);
        $restClient = new RestClient($invalidUri);
    }
}
