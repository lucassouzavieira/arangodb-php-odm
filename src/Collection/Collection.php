<?php
declare(strict_types=1);

namespace ArangoDB\Collection;

use ArangoDB\Database\Database;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Connection\ManagesConnection;
use ArangoDB\Exceptions\DatabaseException;

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
     * Attributes of collection
     *
     * @var array
     */
    protected $attributes;

    /**
     * @var bool
     */
    protected $isNew;

    /**
     * Database object
     *
     * @var Database
     */
    protected $database;

    /**
     * Default values when creating collections
     *
     * @var array
     */
    private $defaults = [
        'journalSize' => 1048576,
        'replicationFactor' => 1,
        'waitForSync' => false,
        'doCompact' => true,
        'shardingStrategy' => 'community-compat',
        'isVolatile' => true,
        'shardKeys' => ["_key"],
        'numberOfShards' => 1,
        'isSystem' => false,
        'type' => 2,
        'indexBuckets' => 16
    ];

    /**
     * Collection constructor.
     *
     * @param string $name
     * @param Database $database
     * @param array $attributes
     * @throws DatabaseException|GuzzleException
     */
    public function __construct(string $name, Database $database, array $attributes = [])
    {
        $this->name = $name;
        $this->database = $database;
        $this->attributes = array_merge($this->defaults, $attributes, ['name' => $name]);
        $this->connection = $database->getConnection();
        $this->isNew = !$database->hasCollection($name);
    }

    /**
     * Get some attribute
     *
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return null;
    }

    /**
     * Set a attribute
     *
     * @param string $name
     * @param mixed $value
     * @throws \Exception
     */
    public function __set(string $name, $value)
    {
        // Allow only one of defaults attributes to be set.
        if (array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = $value;
            return;
        }

        throw new \Exception("Non-default collection property with name: ($name)");
    }

    /**
     * Returns the database where collection belongs
     *
     * @return Database
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * Saves the collection. When
     *
     * @return bool
     */
    public function save(): bool
    {
    }
}
