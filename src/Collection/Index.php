<?php
declare(strict_types=1);

namespace ArangoDB\Collection;

use ArangoDB\Entity\EntityInterface;

/**
 * Class Index
 * Represents a collection index
 *
 * @package ArangoDB\Collection
 * @author Lucas S. Vieira
 */
class Index implements EntityInterface
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
     * Minimum length of index
     *
     * @var int
     */
    protected $minLength;

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
     * Return index minimum length
     *
     * @return int
     */
    public function getMinLength(): int
    {
        return $this->minLength;
    }

    /**
     * Saves the index on server, if possible
     * @return bool true if operation was successful, false otherwise
     * @see EntityInterface::save()
     */
    public function save(): bool
    {
        return false;
    }

    /**
     * Removes a entity on server, if possible
     *
     * @return bool true if operation was successful, false otherwise
     */
    public function delete(): bool
    {
        return false;
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
            'minLength' => $this->getMinLength()
        ];
    }

    /**
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
