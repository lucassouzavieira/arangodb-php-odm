<?php
declare(strict_types=1);

namespace ArangoDB\Collection\Index;

use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Class SkipListIndex
 * Represents a skip-list index on a collection
 *
 * @package ArangoDB\Collection\Index
 * @author Lucas S. Vieira
 */
final class SkipListIndex extends HashIndex
{
    /**
     * SkipListIndex constructor.
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
        parent::__construct($fields, $unique, $sparse, $deduplicate);
        $this->type = "skiplist";
    }
}
