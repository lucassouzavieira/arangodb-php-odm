<?php

declare(strict_types=1);

namespace ArangoDB\Collection\Index;

use ArangoDB\Collection\Collection;
use ArangoDB\Collection\Contracts\IndexInterface;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Represents a collection index.
 *
 * @package ArangoDB\Collection
 * @author Lucas S. Vieira
 */
class Index implements IndexInterface
{
    /**
     * Index ID.
     *
     * @var string
     */
    protected string $id;

    /**
     * Index name.
     *
     * @var string
     */
    protected string $name;

    /**
     * Sparse
     *
     * @var bool
     */
    protected bool $sparse;

    /**
     * Index type
     *
     * @var string
     */
    protected string $type;

    /**
     * Unique constraint
     *
     * @var bool
     */
    protected bool $unique;

    /**
     * Fields of index
     *
     * @var array
     */
    protected array $fields;

    /**
     * Collection where the index belongs to
     *
     * @var Collection
     */
    protected $collection;

    /**
     * If the index is a new one or
     * a representation of an existing index
     *
     * @var bool
     */
    protected bool $isNew;

    /**
     * Valid indexes types
     *
     * @var array
     */
    protected static array $indexTypes = [
        'fulltext', 'general', 'geo', 'hash', 'persistent', 'skiplist', 'ttl', 'primary', 'edge', 'inverted'
    ];

    /**
     * Index constructor.
     *
     * @param string $type The index type. Must be one of following values: 'fulltext', 'general', 'geo', 'hash', 'persistent', 'skiplist', 'inverted' or 'ttl'
     * @param array $fields An array of attribute names. Normally with just one attribute.
     *
     * @param array $attributes
     * @throws InvalidParameterException
     */
    public function __construct(string $type, array $fields, array $attributes = [])
    {
        if (!in_array($type, self::$indexTypes)) {
            throw new InvalidParameterException("type", $type);
        }

        $fieldNames = [];
        foreach ($fields as $key => $field) {
            if (is_string($field)) {
                $fieldNames[] = $field;
                continue;
            }

            if (is_array($field)) {
                $fieldNames[] = $field['name'];
                continue;
            }

            throw new InvalidParameterException("fields[$key]", $field);
        }

        $this->type = $type;
        $this->fields = $fieldNames;

        // Default values;
        $this->id = $attributes['id'] ?? '';
        $this->name = $attributes['name'] ?? '';
        $this->unique = $attributes['unique'] ?? false;
        $this->sparse = $attributes['sparse'] ?? false;
        $this->isNew = !isset($attributes['id']);
    }

    /**
     * String representation of index
     *
     * @return mixed
     */
    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    /**
     * @return bool True if is a new object. False otherwise.
     * @see EntityInterface::isNew()
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * If index has 'unique' constraint
     *
     * @return bool
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

    /**
     * If the index is sparse
     *
     * @return bool
     */
    public function isSparse(): bool
    {
        return $this->sparse;
    }

    /**
     * Index Id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns index name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns index type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns index fields
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Returns the collection where index belong to
     *
     * @return Collection|null A collection object or null if the index was not set to an collection yet
     */
    public function getCollection(): ?Collection
    {
        return $this->collection;
    }

    /**
     * Set the collection where the index belongs to
     *
     * @param Collection $collection
     */
    public function setCollection(Collection $collection): void
    {
        $this->collection = $collection;
    }

    /**
     * Returns an array representation of entity
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'isNew' => $this->isNew(),
            'sparse' => $this->isSparse(),
            'type' => $this->getType(),
            'unique' => $this->isUnique(),
            'fields' => $this->getFields(),
        ];
    }

    /**
     * Return data for create index on server
     *
     * @return array
     */
    public function getCreateData(): array
    {
        return $this->toArray();
    }

    /**
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
