<?php
declare(strict_types=1);

namespace ArangoDB\Collection\Index;

use ArangoDB\Exceptions\IndexException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Represents a primary index on a collection
 *
 * @package ArangoDB\Collection\Index
 * @author Lucas S. Vieira
 */
class PrimaryIndex extends Index
{
    /**
     * PrimaryIndex constructor.
     *
     * @param array $fields
     * @param array $attributes
     * @throws InvalidParameterException
     */
    public function __construct(array $fields, array $attributes = [])
    {
        parent::__construct("primary", $fields, $attributes);
    }

    /**
     * This type of Index cannot be create/deleted explicitly by user.
     * Throws an exception if an attempt of create one is made
     *
     * @return array
     * @throws IndexException
     */
    public function getCreateData(): array
    {
        throw new IndexException("Cannot create an index of type $this->type explicitly");
    }
}
