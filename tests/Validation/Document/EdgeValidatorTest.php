<?php


namespace Unit\Validation\Document;

use Unit\TestCase;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Validation\Document\EdgeValidator;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

class EdgeValidatorTest extends TestCase
{
    public function getMockAttributes()
    {
        return [
            '_from' => 'cities/sao_luis',
            '_to' => 'cities/forteleza',
            'flightNumber' => 123,
            'flightHour' => '06h00 UTC',
            'checkinStatus' => 'doing'
        ];
    }

    public function testValidator()
    {
        $attributes = $this->getMockAttributes();
        $validator = new EdgeValidator($attributes);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowInvalidParameterException()
    {
        $attributes = $this->getMockAttributes();
        $attributes['tr'] = new ArrayList(['with' => ['some', 'fields']]); // Add object to attributes
        $validator = new EdgeValidator($attributes);
        $this->expectException(InvalidParameterException::class);
        $validator->validate();
    }

    public function testValidatorThrowMissingParameterExceptionForToParam()
    {
        // Missing '_to'
        $attributes = $this->getMockAttributes();
        unset($attributes['_to']);

        $validator = new EdgeValidator($attributes);
        $this->expectException(MissingParameterException::class);
        $validator->validate();

    }

    public function testValidatorThrowMissingParameterExceptionForFromParam()
    {
        // Missing '_from'
        $attributes = $this->getMockAttributes();
        unset($attributes['_from']);

        $validator = new EdgeValidator($attributes);
        $this->expectException(MissingParameterException::class);
        $validator->validate();
    }

    public function testValidatorThrowInvalidParameterExceptionForToParam()
    {
        // Invalid '_to'
        $attributes = $this->getMockAttributes();
        $attributes['_to'] = 544;

        $validator = new EdgeValidator($attributes);
        $this->expectException(InvalidParameterException::class);
        $validator->validate();
    }

    public function testValidatorThrowInvalidParameterExceptionForFromParam()
    {
        // Invalid '_from'
        $attributes = $this->getMockAttributes();
        $attributes['_from'] = 8459;

        $validator = new EdgeValidator($attributes);
        $this->expectException(InvalidParameterException::class);
        $validator->validate();
    }
}
