<?php


namespace Unit\Graph;

use Unit\TestCase;
use ArangoDB\Graph\EdgeDefinition;

class EdgeDefinitionTest extends TestCase
{
    public function testGetCollection()
    {
        $definition = new EdgeDefinition('relationship', ['male', 'female'], ['male', 'female']);
        $this->assertEquals('relationship', $definition->getCollection());
    }

    public function testTo()
    {
        $definition = new EdgeDefinition('relationship', ['male', 'female'], ['male', 'female']);
        $to = $definition->to();
        $this->assertIsArray($to);
        $this->assertTrue(in_array('male', $to));
        $this->assertTrue(in_array('female', $to));
    }

    public function testFrom()
    {
        $definition = new EdgeDefinition('relationship', ['male', 'female'], ['male', 'female']);
        $from = $definition->from();
        $this->assertIsArray($from);
        $this->assertTrue(in_array('male', $from));
        $this->assertTrue(in_array('female', $from));
    }
}
