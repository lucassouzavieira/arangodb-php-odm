<?php
declare(strict_types=1);

namespace ArangoDB\Collection\Index;

use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Class HashIndex
 * Represents a hash index on a collection
 *
 * @package ArangoDB\Collection\Index
 * @author Lucas S. Vieira
 */
class HashIndex extends Index
{
    /**
     * If set to false, the deduplication of array values is turned off
     *
     * @var bool
     */
    protected $deduplicate;

    /**
     * HashIndex constructor.
     *
     * @param array $fields An array of attribute names.
     * @param bool $unique If true, then create an unique index
     * @param bool $sparse If true, then create a sparse index
     * @param bool $deduplicate If true, turn on the deduplication of array values
     *
     * @throws InvalidParameterException
     */
    public function __construct(array $fields, bool $unique = true, bool $sparse = true, bool $deduplicate = true)
    {
        parent::__construct("hash", $fields);
        $this->unique = $unique;
        $this->sparse = $sparse;
        $this->deduplicate = $deduplicate;
    }

    /**
     * Return the deduplicate parameter
     *
     * @return bool
     */
    public function isDeduplicate(): bool
    {
        return $this->deduplicate;
    }

    /**
     * Return data for create index on server
     *
     * @return array
     */
    public function getCreateData(): array
    {
        return [
            'type' => $this->getType(),
            'fields' => $this->getFields(),
            'unique' => $this->isUnique(),
            'sparse' => $this->isSparse(),
            'deduplicate' => $this->isDeduplicate()
        ];
    }

    /**
     * Returns a array representation of index
     *
     * @return array
     */
    public function toArray(): array
    {
        $values = parent::toArray();
        $values['deduplicate'] = $this->isDeduplicate();
        return $values;
    }
}
