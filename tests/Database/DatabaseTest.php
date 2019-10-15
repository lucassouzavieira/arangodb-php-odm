<?php


namespace Unit\Database;

use Unit\TestCase;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Database\DatabaseHandler;
use ArangoDB\Exceptions\DatabaseException;

class DatabaseTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }
}
