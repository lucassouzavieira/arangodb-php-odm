<?php

namespace Unit\Validation\Transaction;

use Unit\TestCase;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Validation\Auth\UserValidator;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

class UserValidatorTest extends TestCase
{
    public function getOptions()
    {
        return [
            'user' => 'Username',
            'password' => 'secret',
            'active' => true,
            'extra' => [
                'some' => 'Extra data'
            ]
        ];
    }

    public function testValidator()
    {
        $attributes = $this->getOptions();
        $validator = new UserValidator($attributes);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowMissingParameterException()
    {
        $attributes = $this->getOptions();
        unset($attributes['user']);
        $validator = new UserValidator($attributes);

        $this->expectException(MissingParameterException::class);
        $this->assertTrue($validator->validate());

        $attributes = $this->getOptions();
        unset($attributes['active']);
        $validator = new UserValidator($attributes);

        $this->expectException(MissingParameterException::class);
        $this->assertTrue($validator->validate());
    }

    public function testValidatorThrowInvalidParameterException()
    {
        $attributes = $this->getOptions();
        $attributes['user'] = new ArrayList();
        $validator = new UserValidator($attributes);

        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($validator->validate());

        $attributes = $this->getOptions();
        $attributes['extra'] = null;
        $validator = new UserValidator($attributes);

        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($validator->validate());
    }
}
