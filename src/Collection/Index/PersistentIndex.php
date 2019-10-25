<?php
declare(strict_types=1);

namespace ArangoDB\Collection\Index;

use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Class PersistentIndex
 * Represents a persistent index on a collection
 *
 * @package ArangoDB\Collection\Index
 * @author Lucas S. Vieira
 */
final class PersistentIndex extends Index
{
    /**
     * PersistentIndex constructor.
     *
     * @param array $fields
     * @param bool $unique
     * @param bool $sparse
     *
     * @throws InvalidParameterException
     */
    public function __construct(array $fields, bool $unique = true, bool $sparse = true)
    {
        parent::__construct("persistent", $fields);
        $this->unique = $unique;
        $this->sparse = $sparse;
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
