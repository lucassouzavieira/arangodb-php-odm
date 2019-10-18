<?php


namespace Unit\AQL;

use Unit\TestCase;
use ArangoDB\AQL\BindContainer;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

class BindContainterTest extends TestCase
{
    public function testPut()
    {
        $container = new BindContainer();
        $container->put('@age', 10);
        $container->put('@height', 1.70);
        $container->put('@single', false);
        $container->put('@parents', ['mother' => 'Marta', 'father' => 'Peter']);

        $this->assertTrue($container->has('@age'));
        $this->assertTrue($container->has('@height'));
    }

    public function testPutThrowInvalidParameterException()
    {
        $container = new BindContainer();
        $this->expectException(InvalidParameterException::class);
        $container->put('@age', new ArrayList());
    }
}
