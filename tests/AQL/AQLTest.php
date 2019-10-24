<?php

namespace Unit\AQL;

use ArangoDB\AQL\Functions\AQLFunction;
use ArangoDB\Exceptions\ServerException;
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

    public function testFunctions()
    {
        $attributes = [
            'name' => "myfunctions::utils::square",
            'code' => "function(x){\n return x*x; \n}",
            'isDeterministic' => true,
        ];

        list($name, $code, $isDeterministic) = array_values($attributes);
        $fn = new AQLFunction($name, $code, $this->getConnectionObject());
        $this->assertEquals(0, count(AQL::functions($this->getConnectionObject())));

        $this->assertTrue($fn->isNew());
        $this->assertTrue($fn->save());

        $list = AQL::functions($this->getConnectionObject());
        $this->assertEquals(1, count(AQL::functions($this->getConnectionObject())));
        $this->assertEquals($attributes['name'], $list->first()->getName());
        $this->assertTrue($fn->delete());
    }

    public function testFunctionsFromNamespace()
    {
        $attributesSquare = [
            'name' => "myfunctions::utils::square",
            'code' => "function(x){\n return x*x; \n}",
            'isDeterministic' => true,
        ];

        $attributesPow = [
            'name' => "myfunctions::math::pow",
            'code' => "function(x, y){\n return x**y; \n}",
            'isDeterministic' => true,
        ];

        // Create 2 functions in different namespaces
        list($name, $code, $isDeterministic) = array_values($attributesSquare);
        $fnSquare = new AQLFunction($name, $code, $this->getConnectionObject());
        list($name, $code, $isDeterministic) = array_values($attributesPow);
        $fnPow = new AQLFunction($name, $code, $this->getConnectionObject());
        $this->assertTrue($fnSquare->save());
        $this->assertTrue($fnPow->save());

        // We must have 2 functions on server
        $this->assertEquals(2, count(AQL::functions($this->getConnectionObject())));

        $utilsNamespace = AQL::functions($this->getConnectionObject(), "myfunctions::utils");
        $this->assertEquals(1, count($utilsNamespace));

        $mathNamespace = AQL::functions($this->getConnectionObject(), "myfunctions::math");
        $this->assertEquals(1, count($mathNamespace));

        $this->assertTrue($fnSquare->delete());
        $this->assertTrue($fnPow->delete());
        $this->assertEquals(0, count(AQL::functions($this->getConnectionObject())));
    }

    public function testFunctionsThrowServerException()
    {
        $mock = new MockHandler([
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $this->expectException(ServerException::class);
        $list = AQL::functions($this->getConnectionObject($mock));
    }
}
