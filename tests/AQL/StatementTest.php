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
        $this->assertStringContainsString("'Theo'", $statement->toAql());
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

    public function testBindValueCollection()
    {
        $query = "FOR u IN @collection
                FILTER u.id == @id && u.status == @status
                RETURN u";

        $statement = new Statement($query);

        $this->assertTrue($statement->bindValue('@collection', 'users'));
        $this->assertTrue($statement->bindValue('@id', 50));
        $this->assertTrue($statement->bindValue('@status', true));

        $this->assertIsString($statement->toAql());
    }

    public function testGetBindVars()
    {
        $query = "FOR u IN @collection
                FILTER u.id == @id && u.status == @status
                RETURN u";

        $statement = new Statement($query);

        $this->assertTrue($statement->bindValue('@collection', 'users'));
        $this->assertTrue($statement->bindValue('@id', 50));
        $this->assertTrue($statement->bindValue('@status', true));

        $bindVars = $statement->getBindVars();
        $this->assertArrayHasKey('collection', $bindVars);
        $this->assertArrayHasKey('id', $bindVars);
        $this->assertArrayHasKey('status', $bindVars);

        $this->assertTrue($bindVars['status']);
    }

    public function testHasAliases()
    {
        $query = "FOR u IN users RETURN u";
        $statement = new Statement($query);
        $this->assertFalse($statement->hasAliases());

        $query = "FOR u IN @collection RETURN u";
        $statement = new Statement($query);
        $this->assertTrue($statement->hasAliases());
    }

    public function testGetQuery()
    {
        $query = "FOR u IN @collection RETURN u";
        $statement = new Statement($query);

        $this->assertEquals($query, $statement->getQuery());
    }

    public function testToString()
    {
        $query = "FOR u IN @collection RETURN u";
        $statement = new Statement($query);

        $this->assertEquals($query, (string)$statement);
    }
}
