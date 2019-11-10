<?php
declare(strict_types=1);

namespace ArangoDB\Document;

use ArangoDB\Collection\Collection;
use ArangoDB\Database\Database;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Exceptions\DatabaseException;
use ArangoDB\Validation\Document\EdgeValidator;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Represents an ArangoDB Edge document
 *
 * @package ArangoDB\Document
 * @author Lucas S. Vieira
 */
class Edge extends Document
{
    /**
     * The edges '_to' attribute
     *
     * @var string
     */
    protected $to;

    /**
     * The edges '_from' attribute
     *
     * @var string
     */
    protected $from;

    /**
     * Database object
     *
     * @var Database
     */
    protected $database;

    /**
     * Edge constructor.
     *
     * @param array $attributes
     * @param Collection|null $collection
     *
     * @throws InvalidParameterException|MissingParameterException
     */
    public function __construct(array $attributes = [], Collection $collection = null)
    {
        $validator = new EdgeValidator($attributes);
        $validator->validate();
        $this->to = $attributes['_to'];
        $this->from = $attributes['_from'];

        if ($collection) {
            $this->database = $collection->getDatabase();
        }

        parent::__construct($attributes, $collection);
    }

    /**
     * Return the '_to' document
     *
     * @return Vertex|false
     * @throws InvalidParameterException|MissingParameterException|DatabaseException|GuzzleException
     */
    public function to()
    {
        return $this->collection->findByKey($this->to);
    }

    /**
     * Return the '_from' document
     *
     * @return Vertex|false
     * @throws InvalidParameterException|MissingParameterException|DatabaseException|GuzzleException
     */
    public function from()
    {
        return $this->collection->findByKey($this->from);
    }
}
