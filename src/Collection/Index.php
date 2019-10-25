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
     * @return bool
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

    /**
     * @return bool
     */
    public function isSparse(): bool
    {
        return $this->sparse;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
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
            'minLength' => $this->minLength()
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
