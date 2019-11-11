<?php
declare(strict_types=1);

namespace ArangoDB\Collection\Index;

use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
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
     * Default options for hash index
     *
     * @var array
     */
    protected $defaultsOptions = [
        'unique' => true,
        'sparse' => true,
        'deduplicate' => true
    ];

    /**
     * HashIndex constructor.
     *
     * @param array $fields An array of attribute names.
     * @param array $attributes
     *
     * @throws InvalidParameterException
     */
    public function __construct(array $fields, array $attributes = [])
    {
        $attributes = array_merge($this->defaultsOptions, $attributes);
        parent::__construct("hash", $fields);
        $this->deduplicate = isset($attributes['deduplicate']) ? $attributes['deduplicate'] : true;
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
