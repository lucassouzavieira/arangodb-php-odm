<?php


namespace Validation;

use Unit\TestCase;
use ArangoDB\Validation\ConnectionOptionsValidator;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

class ConnectionOptionsValidatorTest extends TestCase
{
    public function testRules()
    {
        $connectionOptions = new ConnectionOptionsValidator([
            'endpoint' => 'http://localhost:8529',
            'database' => '_system',
            'user' => 'someUser',
            'pwd' => 'somePasswd',
        ]);

        $this->assertIsArray($connectionOptions->rules());
    }

    public function testValidate()
    {
        $connectionOptions = new ConnectionOptionsValidator([
            'endpoint' => 'http://localhost:8529',
            'database' => '_system',
            'user' => 'someUser',
            'pwd' => 'somePasswd',
        ]);

        $this->assertTrue($connectionOptions->validate());
    }

    public function testThrowMissingParameterExceptionForRequired()
    {
        $connectionOptions = new ConnectionOptionsValidator([
            'database' => '_system',
            'user' => 'someUser',
            'pwd' => 'somePasswd',
        ]);

        $this->expectException(MissingParameterException::class);
        $this->assertTrue($connectionOptions->validate());
    }

    public function testThrowInvalidParameterException()
    {
        $connectionOptions = new ConnectionOptionsValidator([
            'endpoint' => 'http://localhost:8529',
            'database' => '_system',
            'user' => 'someUser',
            'pwd' => 'somePasswd',
            'connection' => 'Open'
        ]);

        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($connectionOptions->validate());
    }
}
