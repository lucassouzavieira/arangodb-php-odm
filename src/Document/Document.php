<?php
declare(strict_types=1);

namespace ArangoDB\Document;

use ArangoDB\Collection\Collection;
use ArangoDB\Entity\EntityInterface;
use ArangoDB\Validation\Document\DocumentValidator;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Represents an ArangoDB document
 *
 * @package ArangoDB\Auth
 * @author Lucas S. Vieira
 */
class Document implements \JsonSerializable, EntityInterface
{
    /**
     * Document ID
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
     * Collection where document belongs
     *
     * @var Collection
     */
    protected $collection;

    /**
     * Document constructor.
     *
     * @param Collection $collection
     * @param array $attributes
     * @throws InvalidParameterException
     */
    public function __construct(Collection $collection, array $attributes = [])
    {
        $validator = new DocumentValidator($attributes);
        $validator->validate();
        $this->attributes = $attributes;
        $this->collection = $collection;
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
     */
    public function __set(string $name, $value)
    {
        // Allow defaults attributes to be set.
        if (array_key_exists($name, $this->attributes)) {
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
     * Save a entity on server, if possible
     *
     * @return bool true if operation was successful, false otherwise
     */
    public function save(): bool
    {
        // TODO: Implement save() method.
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
