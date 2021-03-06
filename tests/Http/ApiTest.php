<?php


namespace Unit\Http;

use Unit\TestCase;
use ArangoDB\Http\Api;

class ApiTest extends TestCase
{
    public function testBuildDatabaseUri()
    {
        $baseUri = "http://localhost:8660";
        $db = "testdb";

        $endpoint = Api::USER;
        $expected = "http://localhost:8660/_db/testdb/_api/user";
        $this->assertEquals($expected, Api::buildDatabaseUri($baseUri, $db, $endpoint));

        $endpoint = Api::ADMIN_LOG;
        $expected = "http://localhost:8660/_db/testdb/_admin/log";
        $this->assertEquals($expected, Api::buildDatabaseUri($baseUri, $db, $endpoint));

        $endpoint = Api::ALL;
        $expected = "http://localhost:8660/_db/testdb/_api/simple/all";
        $this->assertEquals($expected, Api::buildDatabaseUri($baseUri, $db, $endpoint));

        $endpoint = Api::UPLOAD;
        $expected = "http://localhost:8660/_db/testdb/_api/upload";
        $this->assertEquals($expected, Api::buildDatabaseUri($baseUri, $db, $endpoint));
    }

    public function testBuildSystemUri()
    {
        $baseUri = "http://localhost:8660";
        $db = "testdb";

        $endpoint = Api::DATABASE;
        $expected = "http://localhost:8660/_api/database";
        $this->assertEquals($expected, Api::buildSystemUri($baseUri, $endpoint));

        $endpoint = Api::CURRENT_DATABASE;
        $expected = "http://localhost:8660/_api/database/current";
        $this->assertEquals($expected, Api::buildSystemUri($baseUri, $endpoint));
    }

    public function testAddQuery()
    {
        $baseUri = "http://localhost:8660";
        $db = "testdb";

        $endpoint = Api::DATABASE;
        $query = ['param' => 'value'];
        $expected = "http://localhost:8660/_api/database?param=value";
        $uri = Api::buildSystemUri($baseUri, $endpoint);
        $this->assertEquals($expected, Api::addQuery($uri, $query));

        $endpoint = Api::CURRENT_DATABASE;
        $expected = "http://localhost:8660/_api/database/current?pa=a&pb=b";
        $query = ['pa' => 'a', 'pb' => 'b'];
        $uri = Api::buildSystemUri($baseUri, $endpoint);
        $this->assertEquals($expected, Api::addQuery($uri, $query));
    }
}
