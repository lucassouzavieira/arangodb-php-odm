<?php


namespace Unit\AQL;

use Unit\TestCase;
use ArangoDB\AQL\Statement;
use ArangoDB\AQL\Exceptions\StatementException;

class StatementTest extends TestCase
{
    public function testToAql()
    {
        $query = "FOR u IN users
                FILTER u.id == @id && u.name == @name && u.status == @status
                RETURN u";

        $statement = new Statement($query);

        $this->assertTrue($statement->bindValue('@id', 50));
        $this->assertTrue($statement->bindValue('@name', 'Theo'));
        $this->assertTrue($statement->bindValue('@status', false));

        $this->assertStringContainsString('false', $statement->toAql());
        $this->assertStringContainsString('50', $statement->toAql());
        $this->assertStringContainsString("\'Theo\'", $statement->toAql());
    }

    public function testToAqlThrowStatementException()
    {
        $query = "FOR u IN users
                FILTER u.id == @id && u.name == @name && u.status == @status
                RETURN u";

        $statement = new Statement($query);

        $this->assertTrue($statement->bindValue('@id', 50));
        $this->assertTrue($statement->bindValue('@name', 'Theo'));

        $this->expectException(StatementException::class);
        $res = $statement->toAql();
    }

    public function testBindValue()
    {
        $query = "FOR u IN users
                FILTER u.id == @id && u.name == @name && u.status == @status
                RETURN u";

        $statement = new Statement($query);

        $this->assertTrue($statement->bindValue('@id', 50));
        $this->assertFalse($statement->bindValue('@age', 1));
    }
}
