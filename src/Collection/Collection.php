<?php
declare(strict_types=1);

namespace ArangoDB\Collection;

use ArangoDB\Http\Api;
use ArangoDB\Database\Database;
use GuzzleHttp\Exception\ClientException;
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
     * Fields to be set directly
     *
     * @var array
     */
    protected $descriptorAttributes = [
        'objectId' => null,
        'name' => '',
        'type' => 2,
        'status' => 0,
        'cacheEnabled' => false,
        'isSystem' => false,
        'globallyUniqueId' => null
    ];

    /**
     * Status strings
     *
     * @var array
     */
    protected $statusStrings = [
        0 => 'unknown',
        1 => 'unknown',
        2 => 'unloaded',
        3 => 'loaded',
        4 => 'unloading',
        5 => 'deleted',
        6 => 'loading'
    ];

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
        'isVolatile' => false,
        'shardKeys' => ["_key"],
        'numberOfShards' => 1,
        'isSystem' => false,
        'type' => 2,
        'keyOptions' => [
            'allowUserKeys' => true,
            'type' => 'traditional',
            'lastValue' => 0
        ],
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
        $this->database = $database;
        $this->attributes = array_merge($this->defaults, $this->descriptorAttributes, $attributes, ['name' => $name]);
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
        // Allow defaults attributes to be set.
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
     * Return the name of collection
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->attributes['name'];
    }

    /**
     * Return the ID of collection
     *
     * @return string|null String if collection exists on database. Null if not.
     */
    public function getId()
    {
        return $this->attributes['objectId'];
    }

    /**
     * Return the globally Unique ID of collection
     *
     * @return string
     */
    public function getGloballyUniqueId(): string
    {
        return $this->attributes['globallyUniqueId'];
    }

    /**
     * Return the status of collection
     *
     * @return int A integer between 0 and 6
     */
    public function getStatus(): int
    {
        return $this->attributes['status'];
    }

    /**
     * Return a string description of status
     *
     * @return string
     */
    public function getStatusDescription(): string
    {
        return $this->statusStrings[$this->getStatus()];
    }

    /**
     * Checks if the collection is a system collection
     *
     * @return bool True if is a system collection. False otherwise.
     */
    public function isSystem(): bool
    {
        return $this->attributes['isSystem'];
    }

    /**
     * Saves the collection.
     *
     * @return bool
     * @throws DatabaseException|GuzzleException
     */
    public function save(): bool
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabase()->getDatabaseName(), Api::COLLECTION);
            $response = $this->connection->post($uri, $this->getCreateParameters());
            $data = json_decode((string)$response->getBody(), true);

            // Update object.
            $this->isNew = false;
            $this->syncCollectionParameters($data);

            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Return only fields to be sent on a POST request
     *
     * @return array
     */
    protected function getCreateParameters(): array
    {
        $arr = [];
        foreach ($this->attributes as $key => $attribute) {
            if (array_key_exists($key, $this->defaults)) {
                $arr[$key] = $attribute;
            }
        }

        // Name is required.
        $arr['name'] = $this->attributes['name'];

        return $arr;
    }

    /**
     * Sync parameters of Collection object after the save or update operation
     *
     * @param array $data
     */
    protected function syncCollectionParameters(array $data): void
    {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            }
        }
    }
}
