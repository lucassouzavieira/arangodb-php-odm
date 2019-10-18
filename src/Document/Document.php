<?php
declare(strict_types=1);

namespace ArangoDB\Document;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use ArangoDB\Collection\Collection;
use ArangoDB\Entity\EntityInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ClientException;
use ArangoDB\Connection\ManagesConnection;
use ArangoDB\Exceptions\DatabaseException;
use ArangoDB\Validation\Rules\RuleInterface;
use ArangoDB\Validation\Document\DocumentValidator;
use ArangoDB\Validation\Document\PatchOptionsValidator;
use ArangoDB\Validation\Document\UpdateOptionsValidator;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Represents an ArangoDB document
 *
 * @package ArangoDB\Document
 * @author Lucas S. Vieira
 */
class Document extends ManagesConnection implements \JsonSerializable, EntityInterface
{
    /**
     * Document ID
     *
     * @var string
     */
    protected $id;

    /**
     * Document key
     *
     * @var string
     */
    protected $key;

    /**
     * Document revision
     *
     * @var string
     */
    protected $revision;

    /**
     * If document is a new one or a representation of existing document
     *
     * @var bool
     */
    protected $isNew;

    /**
     * Documents attributes
     *
     * @var array
     */
    protected $attributes;

    /**
     * Connection to be used
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Collection where document belongs
     *
     * @var Collection
     */
    protected $collection;

    /**
     * Validate primitive types
     *
     * @var RuleInterface
     */
    protected $validator;

    /**
     * Default patch options
     *
     * @var array
     */
    protected $patchDefaultOptions = [
        'keepNull' => false,
        'mergeObjects' => true,
        'waitForSync' => true,
        'ignoreRevs' => true,
        'returnOld' => false,
        'returnNew' => true
    ];

    /**
     * Default update options
     *
     * @var array
     */
    protected $updateDefaultOptions = [
        'waitForSync' => true,
        'ignoreRevs' => true,
        'returnOld' => false,
        'returnNew' => true,
        'silent' => false,
    ];

    /**
     * Document constructor.
     *
     * @param Collection $collection
     * @param array $attributes
     * @throws InvalidParameterException
     */
    public function __construct(Collection $collection, array $attributes = [])
    {
        $this->validator = new DocumentValidator($attributes);
        $this->validator->validate();

        $this->isNew = true;
        $this->attributes = $attributes;
        $this->collection = $collection;
        $this->connection = $this->collection->getConnection();
    }

    /**
     * Return an string representation of document
     *
     * @return string
     */
    public function __toString()
    {
        return print_r($this->toArray(), true);
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
     * @throws InvalidParameterException
     */
    public function __set(string $name, $value)
    {
        $this->validator->setData($value);

        if ($this->validator->validate()) {
            $this->attributes[$name] = $value;
        }
    }

    /**
     * Verifies if an attribute is set on document
     *
     * @param string $name
     * @return bool
     */
    public function __isset(string $name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Unset the given attribute of document
     *
     * @param string $name
     */
    public function __unset(string $name)
    {
        unset($this->attributes[$name]);
    }

    /**
     * Returns true if is a new object
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * Returns the document Id
     *
     * @return string|null String if document already exists. Null otherwise (e.g. a new document)
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the document key
     *
     * @return string|null String if document already exists. Null otherwise (e.g. a new document)
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Returns the document revision
     *
     * @return string|null String if document already exists. Null otherwise (e.g. a new document)
     */
    public function getRevision(): string
    {
        return $this->revision;
    }

    /**
     * Save or update the document, if possible
     *
     * @return bool true if operation was successful. Throws an exceptions otherwise
     * @throws DatabaseException|GuzzleException|InvalidParameterException|MissingParameterException
     */
    public function save(): bool
    {
        try {
            if ($this->isNew()) {
                // If the collection is a new one, we will create this collection on server.
                $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::DOCUMENT);
                $response = $this->connection->post(sprintf("%s/%s", $uri, $this->collection->getName()), $this->attributes);
                $data = json_decode((string)$response->getBody(), true);

                $this->isNew = false;
                $this->id = $data['_id'];
                $this->key = $data['_key'];
                $this->revision = $data['_rev'];
                return true;
            }

            return $this->update($this->updateDefaultOptions);
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Update the document.
     *
     * @todo finish this method
     * @param array $options
     * @return bool
     * @throws DatabaseException|GuzzleException|MissingParameterException|InvalidParameterException
     */
    public function update(array $options = []): bool
    {
        $validator = new UpdateOptionsValidator($options);
        $validator->validate();

        try {
            if ($this->isNew()) {
                // New document cannot be updated. Throw an exception.
                throw new DatabaseException("New document cannot be updated.");
            }

            $attributes = array_merge($this->attributes, ['_key' => $this->key]);
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::DOCUMENT);
            $response = $this->connection->put(sprintf("%s/%s/%s", $uri, $this->collection->getName(), $this->getKey()), $attributes);
            $data = json_decode((string)$response->getBody(), true);

            $this->isNew = false;
            $this->id = $data['_id'];
            $this->key = $data['_key'];
            $this->revision = $data['_rev'];

            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Patch the document.
     *
     * @todo finish this method
     * @param array $options
     * @return bool
     * @throws DatabaseException|GuzzleException|MissingParameterException|InvalidParameterException
     */
    public function patch(array $options = []): bool
    {
        $validator = new PatchOptionsValidator($options);
        $validator->validate();

        $options = array_merge($this->patchDefaultOptions, $options);

        try {
            if ($this->isNew()) {
                // New document cannot be updated. Throw an exception.
                throw new DatabaseException("New document cannot be updated.");
            }

            $attributes = array_merge($this->attributes, ['_key' => $this->key]);
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::DOCUMENT);
            $response = $this->connection->put(sprintf("%s/%s/%s", $uri, $this->collection->getName(), $this->getKey()), $attributes);
            $data = json_decode((string)$response->getBody(), true);

            $this->isNew = false;
            $this->id = $data['_id'];
            $this->key = $data['_key'];
            $this->revision = $data['_rev'];

            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Removes a entity on server, if possible
     *
     * @return bool true if operation was successful, false otherwise
     */
    public function delete(): bool
    {
        // TODO: Implement delete() method.
    }

    /**
     * Returns a array representation of document
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_merge([
            '_id' => $this->id,
            '_rev' => $this->revision,
            '_key' => $this->revision
        ], $this->attributes);
    }

    /**
     * @return array|mixed
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
