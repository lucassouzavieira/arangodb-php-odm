<?php
declare(strict_types=1);

namespace ArangoDB\Document;

use ArangoDB\Collection\Collection;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Represents an ArangoDB Edge document
 *
 * @package ArangoDB\Document
 * @author Lucas S. Vieira
 */
class Edge extends Document
{
    /**
     * The edges 'to' attribute
     *
     * @var string
     */
    protected $to;

    /**
     * The edges 'from' attribute
     *
     * @var string
     */
    protected $from;

    /**
     * Edge constructor.
     *
     * @param array $attributes
     * @param Collection|null $collection
     *
     * @throws InvalidParameterException
     */
    public function __construct(array $attributes = [], Collection $collection = null)
    {
        parent::__construct($attributes, $collection);
    }
}
