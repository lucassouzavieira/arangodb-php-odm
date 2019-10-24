<?php
declare(strict_types=1);

namespace ArangoDB\Document;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use ArangoDB\Collection\Collection;
use ArangoDB\Entity\EntityInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ClientException;
use ArangoDB\Exceptions\DatabaseException;
use ArangoDB\Validation\Rules\RuleInterface;
use ArangoDB\Validation\Document\DocumentValidator;
use ArangoDB\Validation\Document\UpdateOptionsValidator;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Represents an ArangoDB document
 *
 * @package ArangoDB\Document
 * @author Lucas S. Vieira
 */
class Document implements EntityInterface
{
    /**
     * Document ID
     *
     * @var string
     */
    protected $id = '';

    /**
     * Document key
     *
     * @var string
     */
    protected $key = '';

    /**
     * Document revision
     *
     * @var string
     */
    protected $revision = '';

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
     * @param array $attributes Document attributes
     * @param Collection $collection Collection to add this document
     * @throws InvalidParameterException
     */
    public function __construct(array $attributes = [], Collection $collection = null)
    {
        $this->validator = new DocumentValidator($attributes);
        $this->validator->validate();

        $this->isNew = true;

        // If document is a old one, must contain the descriptors.
        if ($this->validator->hasDescriptors()) {
            $this->setDescriptors($this->validator->getDescriptorsAttributes());
        }

        // If document is an representation of a existing one.
        if ($this->getId() || $this->getKey() || $this->getRevision()) {
            $this->isNew = false;
        }

        $this->attributes = $this->validator->getAttributes();

        if ($collection) {
            $this->setCollection($collection);
        }
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
        $this->validator->setAttributes($value);

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
     * Returns the document collection
     *
     * @return Collection|null Collection object if is set. Null otherwise.
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Sets the collection to add this document
     *
     * @param Collection $collection
     */
    public function setCollection(Collection $collection): void
    {
        $this->collection = $collection;
        $this->connection = $collection->getDatabase()->getConnection();
    }

    /**
     * Save or update the document, if possible
     *
     * @param array $options Optional array of options. Only used on update operations.
     * @return bool true if operation was successful. Throws an exceptions otherwise
     * @throws DatabaseException|InvalidParameterException|MissingParameterException
     */
    public function save(array $options = []): bool
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

            return $this->update($options);
        } catch (GuzzleException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Update the document.
     *
     * @param array $options
     * @return bool
     * @throws DatabaseException|GuzzleException|MissingParameterException|InvalidParameterException
     */
    protected function update(array $options = []): bool
    {
        $validator = new UpdateOptionsValidator($options);
        $validator->validate();

        try {
            $attributes = array_merge($this->attributes, ['_key' => $this->key]);
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::DOCUMENT);
            $this->connection->put(sprintf("%s/%s/%s", $uri, $this->collection->getName(), $this->getKey()), $attributes);
            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Removes a document on server, if possible
     *
     * @return bool true if operation was successful, false otherwise
     * @throws DatabaseException|GuzzleException
     */
    public function delete(): bool
    {
        try {
            if ($this->isNew()) {
                return false;
            }

            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::DOCUMENT);
            $this->connection->delete(sprintf("%s/%s/%s", $uri, $this->collection->getName(), $this->getKey()));
            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
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

    /**
     * Set document descriptors
     *
     * @param array $descriptors
     */
    protected function setDescriptors(array $descriptors): void
    {
        $this->id = isset($descriptors['_id']) ? $descriptors['_id'] : $this->id;
        $this->key = isset($descriptors['_key']) ? $descriptors['_key'] : $this->key;
        $this->revision = isset($descriptors['_rev']) ? $descriptors['_rev'] : $this->revision;
    }
}
