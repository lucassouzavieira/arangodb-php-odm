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
     * Returns true if is a new object
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * Save or update the document, if possible
     *
     * @return bool true if operation was successful. Throws an exceptions otherwise
     * @throws DatabaseException|GuzzleException
     */
    public function save(): bool
    {
        try {
            // If the collection is a new one, we will create this collection on server.
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::DOCUMENT);
            $response = $this->connection->post(sprintf("%s/%s", $uri, $this->collection->getName()), $this->attributes);
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
