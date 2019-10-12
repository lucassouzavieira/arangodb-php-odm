<?php


namespace Unit\Validation\Rules;

use ArangoDB\Validation\Rules\Rules;
use Unit\TestCase;

class RulesTest extends TestCase
{
    public function testArrRule()
    {
        $validator = Rules::arr();
        $array = ['Array', 'with', ['some', 'values']];

        $this->assertTrue($validator->isValid($array));
        $this->assertFalse($validator->isValid("Pass some string"));
        $this->assertFalse($validator->isValid(rand(1, 100)));
        $this->assertFalse($validator->isValid(45.5));
    }

    public function testStringRule()
    {
        $validator = Rules::string();
        $string = "Queen rocks!";

        $this->assertTrue($validator->isValid($string));
        $this->assertFalse($validator->isValid(['pass', 'an', 'array']));
        $this->assertFalse($validator->isValid(rand(1, 100)));
        $this->assertFalse($validator->isValid(45.5));
    }

    public function testNumericRule()
    {
        $validator = Rules::numeric();
        $string = "We will rock you!";

        $this->assertTrue($validator->isValid('12.4')); // Numeric string.
        $this->assertTrue($validator->isValid(rand(1, 100)));
        $this->assertTrue($validator->isValid(45.5));
        $this->assertFalse($validator->isValid(['pass', 'an', 'array']));
    }

    public function testIntegerRule()
    {
        $validator = Rules::integer();
        $string = "Another string bites the dust";

        $this->assertTrue($validator->isValid(rand(1, 100)));
        $this->assertFalse($validator->isValid(45.5));
        $this->assertFalse($validator->isValid($string));
        $this->assertFalse($validator->isValid(['pass', 'an', 'array']));
    }

    public function testBooleanRule()
    {
        $validator = Rules::boolean();
        $string = "Welcome to the jungle";

        $this->assertTrue($validator->isValid(true));
        $this->assertTrue($validator->isValid(false));
        $this->assertFalse($validator->isValid(10));
        $this->assertFalse($validator->isValid($string));
        $this->assertFalse($validator->isValid(['pass', 'an', 'array']));
    }

    public function testUriRule()
    {
        $validator = Rules::uri();
        $uriStringTcp = "tcp://192.168.10.10/admin/url";
        $uriStringSsl = "ssl://host:7899/admin/url";
        $uriStringHttp = "http://some.host.com/";
        $uriStringHttps = "https://some.host.com/";
        $string = "Welcome to the jungle";

        $this->assertTrue($validator->isValid($uriStringTcp));
        $this->assertTrue($validator->isValid($uriStringSsl));
        $this->assertTrue($validator->isValid($uriStringHttp));
        $this->assertTrue($validator->isValid($uriStringHttps));
        $this->assertFalse($validator->isValid(10));
        $this->assertFalse($validator->isValid($string));
        $this->assertFalse($validator->isValid(['pass', 'an', 'array']));
    }

    public function testInRule()
    {
        $validator = Rules::in(['Bryan', 'Freddie', 'Roger', 'John']);
        $string = "Axl";

        $this->assertTrue($validator->isValid("Bryan"));
        $this->assertTrue($validator->isValid("Roger"));
        $this->assertFalse($validator->isValid(10));
        $this->assertFalse($validator->isValid($string));
        $this->assertFalse($validator->isValid(['pass', 'an', 'array']));
    }
}
