<?php
declare(strict_types=1);

namespace ArangoDB\Collection\Index;

use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Represents an edge index on a collection
 *
 * @package ArangoDB\Collection\Index
 * @author Lucas S. Vieira
 */
final class EdgeIndex extends PrimaryIndex
{
    /**
     * EdgeIndex constructor.
     *
     * @param array $fields
     * @param array $attributes
     * @throws InvalidParameterException
     */
    public function __construct(array $fields, array $attributes = [])
    {
        parent::__construct($fields, $attributes);
        $this->type = "edge";
    }
}
