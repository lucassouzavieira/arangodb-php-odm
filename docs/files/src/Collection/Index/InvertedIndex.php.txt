<?php

namespace ArangoDB\Collection\Index;

use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Inverted index representation
 *
 * @package ArangoDB\Collection\Index
 * @author Lucas S. Vieira
 */
final class InvertedIndex extends Index
{
    /**
     * Default options for inverted index
     *
     * @var array
     */
    protected array $defaultOptions = [
        'unique' => true,
        'sparse' => true,
        'analyzer' => "identity",
        'inBackground' => true, // Keep the collection available for writes during the index creation
        'parallelism' => 2,
    ];

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
        $attributes = array_merge($this->defaultOptions, $attributes);
        parent::__construct('inverted', $fields, $attributes);
    }
}
