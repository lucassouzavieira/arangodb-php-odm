<?php

namespace Unit\AQL;

use Unit\TestCase;
use ArangoDB\AQL\AQL;
use ArangoDB\AQL\Statement;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\AQL\Exceptions\AQLException;

class AQLTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testValidateQueryForValidQuery()
    {
        $statement = new Statement("FOR i IN my_collection RETURN i");
        $this->assertTrue(AQL::validateQuery($statement, $this->getConnectionObject()));

        $statement = new Statement("FOR i IN @collection RETURN i");
        $this->assertTrue(AQL::validateQuery($statement, $this->getConnectionObject()));
    }

    public function testValidateQueryForInvalidQuery()
    {
        $statement = new Statement("FOR i IN 1..100 FILTER i = 1 LIMIT 2 RETURN i * 3");
        $this->assertFalse(AQL::validateQuery($statement, $this->getConnectionObject()));
    }

    public function testValidateQueryThrowAQLException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $statement = new Statement("FOR i IN 1..100 FILTER i = 1 LIMIT 2 RETURN i * 3");
        $this->expectException(AQLException::class);
        $this->assertFalse(AQL::validateQuery($statement, $this->getConnectionObject($mock)));
    }
}
