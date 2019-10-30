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
class EdgeIndex extends PrimaryIndex
{
    /**
     * EdgeIndex constructor.
     *
     * @param array $fields
     * @throws InvalidParameterException
     */
    public function __construct(array $fields)
    {
        parent::__construct($fields);
        $this->type = "edge";
    }
}
