<?php


namespace Unit\Validation\Document;

use Unit\TestCase;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Validation\Document\DocumentValidator;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

class DocumentValidatorTest extends TestCase
{
    public function testValidator()
    {
        $attributes = [
            'field' => 'of soccer',
            'good_music' => [
                'Queen',
                'Motorhead',
                'Anthrax',
                'Metallica',
            ],
            'status' => false,
            'dreamers' => null,
            'value' => 1.5,
            'percent' => 45.4,
            'quantity' => 40
        ];

        $validator = new DocumentValidator($attributes);
        $this->assertTrue($validator->validate());

        $attributes['tr'] = new ArrayList(['with' => ['some', 'fields']]); // Add object to attributes

        $validator = new DocumentValidator($attributes);
        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($validator->validate());
    }
}
