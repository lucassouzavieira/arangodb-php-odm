<?php

namespace Unit\Validation\Document;

use ArangoDB\DataStructures\ArrayList;
use Unit\TestCase;
use ArangoDB\Validation\Document\UpdateOptionsValidator;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

class UpdateOptionsValidatorTest extends TestCase
{
    public function testValidator()
    {
        $attributes = [
            'waitForSync' => true,
            'ignoreRevs' => false,
            'returnOld' => false,
            'returnNew' => true
        ];
        ;

        $validator = new UpdateOptionsValidator($attributes);
        $this->assertTrue($validator->validate());

        $attributes['returnOld'] = new ArrayList(); // Add invalid type to a parameter

        $validator = new UpdateOptionsValidator($attributes);
        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($validator->validate());
    }
}
