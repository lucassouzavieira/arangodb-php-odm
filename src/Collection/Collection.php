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
 * Represents an ArangoDB collection
 *
 * @package ArangoDB\Auth
 * @author Lucas S. Vieira
 */
class Collection extends ManagesConnection implements \JsonSerializable
{
    /**
     * Attributes of collection
     *
     * @var array
     */
    protected $attributes;

    /**
     * If the collection is a new one or a representation of an existing collection on server
     *
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
        'globallyUniqueId' => null,
        'revision' => 0,
        'count' => 0,
        'checksum' => ''
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
     * Unknown status of collection
     *
     * @var int
     */
    protected static $unknownStatus = 1;

    /**
     * Unloaded status of collection
     *
     * @var int
     */
    protected static $unloadedStatus = 2;

    /**
     * Loaded status of collection
     *
     * @var int
     */
    protected static $loadedStatus = 3;

    /**
     * Deleted status of collection
     *
     * @var int
     */
    protected static $deletedStatus = 5;

    /**
     * Loading status of collection
     *
     * @var int
     */
    protected static $loadingStatus = 6;

    /**
     * Unloading status of collection
     *
     * @var int
     */
    protected static $unloadingStatus = 4;

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
     * Return the collection attributes on array
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set collection attributes
     *
     * @param array $data
     */
    public function setAttributes(array $data): void
    {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            }
        }
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
     * Return the checksum of collection metadata
     *
     * @return string
     * @throws DatabaseException|GuzzleException
     */
    public function getChecksum(): string
    {
        try {
            if (isset($this->attributes['checksum']) && $this->attributes['checksum']) {
                return $this->attributes['checksum'];
            }

            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabase()->getDatabaseName(), Api::COLLECTION);
            $response = $this->connection->get(sprintf("%s/%s%s", $uri, $this->getName(), Api::COLLECTION_CHECKSUM));
            $data = json_decode((string)$response->getBody(), true);
            $this->checksum = sprintf("%d", $data['checksum']);
            return $this->checksum;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Return the revision of collection
     *
     * @return string
     * @throws DatabaseException|GuzzleException
     */
    public function getRevision(): string
    {
        try {
            if (isset($this->attributes['revision']) && $this->attributes['revision']) {
                return $this->attributes['revision'];
            }

            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabase()->getDatabaseName(), Api::COLLECTION);
            $response = $this->connection->get(sprintf("%s/%s%s", $uri, $this->getName(), Api::COLLECTION_REVISION));
            $data = json_decode((string)$response->getBody(), true);
            $this->revision = sprintf("%d", $data['revision']);
            return $this->revision;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
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
     * Returns true if is a new object
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * Saves or update the collection.
     * Except for 'waitForSync', 'journalSize' and 'name', a collection can not be modified after creation.
     * For change 'name', the method 'rename' must be used.
     *
     * @return bool
     * @throws DatabaseException|GuzzleException
     */
    public function save(): bool
    {
        try {
            // If the collection is a new one, we will create this collection on server.
            if ($this->isNew()) {
                $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabase()->getDatabaseName(), Api::COLLECTION);
                $response = $this->connection->post($uri, $this->getCreateParameters());
                $data = json_decode((string)$response->getBody(), true);

                // Update object.
                $this->isNew = false;
                $this->setAttributes($data);
                return true;
            }

            return $this->update();
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Drops the collection on database
     *
     * @return bool
     * @throws DatabaseException|GuzzleException
     */
    public function drop(): bool
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabase()->getDatabaseName(), Api::COLLECTION);
            $response = $this->connection->delete(sprintf("%s/%s", $uri, $this->getName()));
            $data = json_decode((string)$response->getBody(), true);
            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Truncate the collection
     *
     * @return bool
     * @throws DatabaseException|GuzzleException
     */
    public function truncate(): bool
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabase()->getDatabaseName(), Api::COLLECTION);
            $response = $this->connection->put(sprintf("%s/%s%s", $uri, $this->getName(), Api::COLLECTION_TRUNCATE));
            $data = json_decode((string)$response->getBody(), true);
            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Loads the collection on server
     *
     * @param bool $count
     * @return bool
     * @throws DatabaseException|GuzzleException
     */
    public function load(bool $count = true): bool
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabase()->getDatabaseName(), Api::COLLECTION);
            $response = $this->connection->put(sprintf("%s/%s%s", $uri, $this->getName(), Api::COLLECTION_LOAD), ['count' => $count]);
            $data = json_decode((string)$response->getBody(), true);
            $this->status = (int)$data['status'];
            return $this->status === self::$loadedStatus;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Unload the collection on server
     *
     * @return bool
     * @throws DatabaseException|GuzzleException
     */
    public function unload(): bool
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabase()->getDatabaseName(), Api::COLLECTION);
            $response = $this->connection->put(sprintf("%s/%s%s", $uri, $this->getName(), Api::COLLECTION_UNLOAD));
            $data = json_decode((string)$response->getBody(), true);
            $this->status = (int)$data['status'];
            return $data['status'] === self::$unloadedStatus;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Rotate journal of collection
     *
     * @return bool
     * @throws DatabaseException|GuzzleException
     */
    public function rotate(): bool
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabase()->getDatabaseName(), Api::COLLECTION);
            $response = $this->connection->put(sprintf("%s/%s%s", $uri, $this->getName(), Api::COLLECTION_ROTATE));
            $data = json_decode((string)$response->getBody(), true);
            return $data['result'];
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Return the number of documents in a collection
     *
     * @return int
     * @throws DatabaseException|GuzzleException
     */
    public function count(): int
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabase()->getDatabaseName(), Api::COLLECTION);
            $response = $this->connection->get(sprintf("%s/%s%s", $uri, $this->getName(), Api::COLLECTION_COUNT));
            $data = json_decode((string)$response->getBody(), true);

            return (int)$data['count'];
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Recalculates the document count of a collection, if it ever becomes inconsistent.
     *
     * @return bool
     * @throws DatabaseException|GuzzleException
     */
    public function recalculateCount(): bool
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabase()->getDatabaseName(), Api::COLLECTION);
            $response = $this->connection->put(sprintf("%s/%s%s", $uri, $this->getName(), Api::COLLECTION_RECALCULATE_COUNT));
            $data = json_decode((string)$response->getBody(), true);
            return (bool)$data['result'];
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Renames the collection
     *
     * @param string $newName
     * @return bool
     * @throws DatabaseException|GuzzleException
     */
    public function rename(string $newName): bool
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabase()->getDatabaseName(), Api::COLLECTION);
            $response = $this->connection->put(sprintf("%s/%s%s", $uri, $this->getName(), Api::COLLECTION_RENAME), ['name' => $newName]);
            $data = json_decode((string)$response->getBody(), true);
            $this->attributes['name'] = $newName;
            return !$data['error'];
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return $this->getAttributes();
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
     * Return only fields to update this collection.
     *
     * @return array
     * @see Collection::rename()
     */
    protected function getUpdateParameters(): array
    {
        return [
            'waitForSync' => $this->attributes['waitForSync'],
            'journalSize' => $this->attributes['journalSize']
        ];
    }

    /**
     * Update collection.
     *
     * @return bool
     * @throws GuzzleException
     */
    protected function update()
    {
        $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->getDatabase()->getDatabaseName(), Api::COLLECTION);
        $response = $this->connection->put(sprintf("%s%s", $uri, Api::COLLECTION_PROPERTIES), $this->getUpdateParameters());
        $data = json_decode((string)$response->getBody(), true);

        // Update object.
        $this->isNew = false;
        $this->setAttributes($data);

        return true;
    }
}
