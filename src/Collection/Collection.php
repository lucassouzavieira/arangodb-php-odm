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
     * Collection ID
     *
     * @var integer|string
     */
    protected $id;

    /**
     * Collection name
     *
     * @var string
     */
    protected $name;

    /**
     * Collection status
     *
     * @var integer
     */
    protected $status;

    /**
     * If is a system collection
     *
     * @var bool
     */
    protected $isSystem;

    /**
     * Globally Unique ID
     *
     * @var string
     */
    protected $globallyUniqueId;

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
