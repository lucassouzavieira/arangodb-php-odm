<?php
declare(strict_types=1);

namespace ArangoDB\Collection\Index;

use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
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
     * @param array $attributes
     *
     * @throws InvalidParameterException
     */
    public function __construct(array $fields, array $attributes = [])
    {
        parent::__construct($fields, $attributes);
        $this->type = "skiplist";
    }
}
