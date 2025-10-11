<?php

namespace ArangoDB\Collection\Index;

use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
* Inverted index representation
*
* @package ArangoDB\Collection\Index
* @author Lucas S. Vieira
*/
class InvertedIndex extends Index
{
    /**
     * InvertedIndex constructor
     *
     * @param array $fields Fields for which the index applies to
     * @param array $attributes Index $attributes
     *
     * @throws InvalidParameterException
     */
    public function __construct(array $fields, array $attributes = [])
    {
        parent::__construct('inverted', $fields, $attributes);
    }
}
