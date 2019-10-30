<?php
declare(strict_types=1);

namespace ArangoDB\Collection\Index;

use ArangoDB\Collection\Collection;
use ArangoDB\Collection\Contracts\IndexInterface;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Class Index
 * Represents a collection index
 *
 * @package ArangoDB\Collection
 * @author Lucas S. Vieira
 */
class Index implements IndexInterface
{
    /**
     * Index Id
     *
     * @var string
     */
    protected $id;

    /**
     * Index name
     *
     * @var string
     */
    protected $name;

    /**
     * Sparse
     *
     * @var bool
     */
    protected $sparse;

    /**
     * Index type
     *
     * @var string
     */
    protected $type;

    /**
     * Unique constraint
     *
     * @var bool
     */
    protected $unique;

    /**
     * Fields of index
     *
     * @var array
     */
    protected $fields;

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
    protected $isNew;

    /**
     * Valid indexes types
     *
     * @var array
     */
    protected static $indexTypes = [
        'fulltext', 'general', 'geo', 'hash', 'persistent', 'skiplist', 'ttl', 'primary', 'edge'
    ];

    /**
     * Index constructor.
     *
     * @param string $type The index type. Must be one of following values: 'fulltext', 'general', 'geo', 'hash', 'persistent', 'skiplist' or 'ttl'
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

        foreach ($fields as $key => $field) {
            if (!is_string($field)) {
                throw new InvalidParameterException("fields[$key]", $field);
            }
        }

        $this->type = $type;
        $this->fields = $fields;

        // Default values;
        $this->id = isset($attributes['id']) ? $attributes['id'] : '';
        $this->name = isset($attributes['name']) ? $attributes['name'] : '';
        $this->unique = isset($attributes['unique']) ? $attributes['unique'] : false;
        $this->sparse = isset($attributes['sparse']) ? $attributes['sparse'] : false;
        $this->isNew = !isset($attributes['id']);
    }

    /**
     * String representation of index
     *
     * @return mixed
     */
    public function __toString()
    {
        return print_r($this->toArray(), true);
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
    public function getCollection()
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
     * Returns a array representation of entity
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
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Static creation of an Index object
     *
     * @param array $attributes
     * @param Collection $collection
     * @return Index
     *
     * @throws InvalidParameterException
     */
    public static function make(array $attributes, Collection $collection): Index
    {
        $minLength = isset($attributes['minLength']) ? $attributes['minLength'] : 0;
        $index = new self($attributes['type'], $attributes['fields'], $attributes);
        $fields = ['id', 'name', 'sparse', 'type', 'unique'];

        foreach ($fields as $field) {
            if (isset($attributes[$field])) {
                $index->{$field} = $attributes[$field];
            }
        }

        $index->isNew = false;
        $index->setCollection($collection);
        return $index;
    }
}
