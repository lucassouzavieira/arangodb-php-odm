<?php


namespace ArangoDB\Collection;

use ArangoDB\Database\Database;
use ArangoDB\Connection\ManagesConnection;

/**
 * Represents a collection of a database
 *
 * @package ArangoDB\Auth
 * @author Lucas S. Vieira
 */
class Collection extends ManagesConnection
{
    /**
     * Database object
     *
     * @var Database
     */
    private $database;

    /**
     * Collection constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->connection = $this->database->getConnection();
    }
}