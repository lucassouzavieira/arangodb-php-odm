<?php


namespace Unit\Validation\Document;

use Unit\TestCase;
use ArangoDB\Validation\Document\PatchOptionsValidator;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

class PatchOptionsValidatorTest extends TestCase
{
    public function testValidator()
    {
        $attributes = [
            'keepNull' => true,
            'mergeObjects' => true,
            'waitForSync' => true,
            'ignoreRevs' => false,
            'returnOld' => false,
            'returnNew' => true
        ];;

        $validator = new PatchOptionsValidator($attributes);
        $this->assertTrue($validator->validate());

        $attributes['returnOld'] = "some_string"; // Add invalid type to a parameter

        $validator = new PatchOptionsValidator($attributes);
        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($validator->validate());
    }
}
