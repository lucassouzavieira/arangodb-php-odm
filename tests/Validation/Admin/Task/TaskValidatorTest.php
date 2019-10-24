<?php


namespace Unit\Validation\Admin\Task;

use Unit\TestCase;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Validation\Admin\Task\TaskValidator;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

class TaskValidatorTest extends TestCase
{
    public function getOptions()
    {
        return [
            'params' => [
                'city' => 'Rio de Janeiro',
                'status' => false,
            ],
            'offset' => 1,
            'command' => "(function(params) { require('@arangodb').print(params); })(params)",
            'name' => 'myTask',
            'period' => 30,
        ];
    }

    public function testValidator()
    {
        $attributes = $this->getOptions();

        $validator = new TaskValidator($attributes);
        $this->assertTrue($validator->validate());
    }

    public function testThrowMissingParameterExceptionOnMissingName()
    {
        $attributes = $this->getOptions();
        unset($attributes['name']);

        $validator = new TaskValidator($attributes);
        $this->expectException(MissingParameterException::class);
        $this->assertTrue($validator->validate());
    }

    public function testThrowMissingParameterExceptionOnMissingCommand()
    {
        $attributes = $this->getOptions();
        unset($attributes['command']);

        $validator = new TaskValidator($attributes);
        $this->expectException(MissingParameterException::class);
        $this->assertTrue($validator->validate());
    }

    public function testThrowInvalidParameterExceptionOnNonPrimitiveValueOnParams()
    {
        $attributes = $this->getOptions();
        $attributes['params'] = [
            'any' => new ArrayList()
        ];

        $validator = new TaskValidator($attributes);
        $this->expectException(InvalidParameterException::class);
        $this->assertTrue($validator->validate());
    }
}
