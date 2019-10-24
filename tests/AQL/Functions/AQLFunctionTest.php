<?php


namespace Unit\AQL\Functions;

use Unit\TestCase;
use ArangoDB\AQL\AQL;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use ArangoDB\AQL\Functions\AQLFunction;
use ArangoDB\Exceptions\ServerException;

class AQLFunctionTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function tearDown(): void
    {
        $functions = AQL::functions($this->getConnectionObject());
        foreach ($functions as $fn) {
            $fn->delete();
        }

        parent::tearDown();
    }

    public function getOptions()
    {
        return [
            'name' => "myfunctions::utils::square",
            'code' => "function(x){\n return x*x; \n}",
            'isDeterministic' => true,
        ];
    }

    public function testConstruct()
    {
        list($name, $code, $isDeterministic) = array_values($this->getOptions());
        $function = new AQLFunction($name, $code);

        $this->assertTrue($function->isNew());
        $this->assertTrue($function->isDeterministic());
        $this->assertFalse($function->hasConnection());
        $this->assertEquals($name, $function->getName());
        $this->assertEquals($code, $function->getCode());
    }

    public function testSave()
    {
        list($name, $code, $isDeterministic) = array_values($this->getOptions());
        $fn = new AQLFunction($name, $code, $this->getConnectionObject());

        $this->assertEquals(0, count(AQL::functions($this->getConnectionObject())));

        $this->assertTrue($fn->isNew());
        $this->assertTrue($fn->save());

        $this->assertEquals(1, count(AQL::functions($this->getConnectionObject())));
    }

    public function testSaveWithoutConnectionReturnFalse()
    {
        list($name, $code, $isDeterministic) = array_values($this->getOptions());
        $fn = new AQLFunction($name, $code);

        $this->assertEquals(0, count(AQL::functions($this->getConnectionObject())));

        $this->assertFalse($fn->save());

        $this->assertEquals(0, count(AQL::functions($this->getConnectionObject())));
    }

    public function testSaveThrowServerException()
    {
        list($name, $code, $isDeterministic) = array_values($this->getOptions());
        // Invalid name
        $fn = new AQLFunction('1548', $code, $this->getConnectionObject());
        $this->expectException(ServerException::class);
        $fn->save();
    }

    public function testDelete()
    {
        list($name, $code, $isDeterministic) = array_values($this->getOptions());
        $fn = new AQLFunction($name, $code, $this->getConnectionObject());

        // Create AQL Function
        $this->assertEquals(0, count(AQL::functions($this->getConnectionObject())));
        $this->assertTrue($fn->isNew());
        $this->assertTrue($fn->save());

        $this->assertEquals(1, count(AQL::functions($this->getConnectionObject())));

        // Check deletion
        $this->assertCount(0, $fn->getDeletionData());
        $this->assertTrue($fn->delete());
        $this->assertCount(3, $fn->getDeletionData());
        $this->assertEquals(0, count(AQL::functions($this->getConnectionObject())));
    }

    public function testDeleteWithoutConnectionReturnFalse()
    {
        list($name, $code, $isDeterministic) = array_values($this->getOptions());
        $fn = new AQLFunction($name, $code);
        $this->assertFalse($fn->delete());
    }

    public function testDeleteThrowServerException()
    {
        $mock = new MockHandler([
            new Response(201, [], json_encode(['name' => '', 'isNewlyCreated' => true, 'error' => false])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        list($name, $code, $isDeterministic) = array_values($this->getOptions());
        $fn = new AQLFunction($name, $code, $this->getConnectionObject($mock));
        $fn->save();

        $this->expectException(ServerException::class);
        $fn->delete();
    }

    public function testSetAndHasConnection()
    {
        list($name, $code, $isDeterministic) = array_values($this->getOptions());
        $fn = new AQLFunction($name, $code);

        $this->assertFalse($fn->hasConnection());
        $fn->setConnection($this->getConnectionObject());
        $this->assertTrue($fn->hasConnection());
    }

    public function testJsonSerialize()
    {
        list($name, $code, $isDeterministic) = array_values($this->getOptions());
        $fn = new AQLFunction($name, $code);
        $this->assertJson(json_encode($fn));
    }
}
