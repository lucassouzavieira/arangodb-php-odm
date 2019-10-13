<?php


namespace Unit\Http;

use ArangoDB\Http\Api;
use Unit\TestCase;

class ApiTest extends TestCase
{
    public function testBuildUri()
    {
        $baseUri = "http://localhost:8660";
        $db = "testdb";

        $endpoint = Api::USER;
        $expected = "http://localhost:8660/_db/testdb/_api/user";
        $this->assertEquals($expected, Api::buildUri($baseUri, $db, $endpoint));

        $endpoint = Api::ADMIN_LOG;
        $expected = "http://localhost:8660/_db/testdb/_admin/log";
        $this->assertEquals($expected, Api::buildUri($baseUri, $db, $endpoint));

        $endpoint = Api::ALL;
        $expected = "http://localhost:8660/_db/testdb/_api/simple/all";
        $this->assertEquals($expected, Api::buildUri($baseUri, $db, $endpoint));

        $endpoint = Api::UPLOAD;
        $expected = "http://localhost:8660/_db/testdb/_api/upload";
        $this->assertEquals($expected, Api::buildUri($baseUri, $db, $endpoint));
    }
}
