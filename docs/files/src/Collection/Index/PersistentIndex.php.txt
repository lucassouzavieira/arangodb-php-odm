<?php
declare(strict_types=1);

namespace ArangoDB\Collection\Index;

use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Represents a persistent index on a collection
 *
 * @package ArangoDB\Collection\Index
 * @author Lucas S. Vieira
 */
final class PersistentIndex extends Index
{
    /**
     * Default options for persistent index
     *
     * @var array
     */
    protected $defaultsOptions = [
        'unique' => true,
        'sparse' => true,
    ];

    /**
     * PersistentIndex constructor.
     *
     * @param array $fields
     * @param array $attributes
     *
     * @throws InvalidParameterException
     */
    public function __construct(array $fields, array $attributes = [])
    {
        $attributes = array_merge($this->defaultsOptions, $attributes);
        parent::__construct("persistent", $fields, $attributes);
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
        ];
    }
}
