<?php
declare(strict_types=1);

namespace ArangoDB\Cursor;

use ArangoDB\AQL\Statement;
use ArangoDB\Document\Edge;
use ArangoDB\Document\Document;
use ArangoDB\Collection\Collection;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Represents an ArangoDB cursor for a collection
 *
 * @package ArangoDB\Cursor
 * @author Lucas S. Vieira
 */
class CollectionCursor extends Cursor
{
    /**
     * Collection object
     *
     * @var Collection
     */
    protected $collection;

    /**
     * CollectionCursor constructor.
     *
     * @param Collection $collection Collection
     *
     * @throws Exceptions\CursorException|InvalidParameterException|GuzzleException
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
        $statement = new Statement("FOR doc IN @collection RETURN doc");
        $statement->bindValue('@collection', $collection->getName());
        $connection = $this->collection->getDatabase()->getConnection();
        parent::__construct($connection, $statement);
    }

    /**
     * @return Document|mixed
     * @throws InvalidParameterException|MissingParameterException
     * @see \Iterator::current()
     */
    public function current()
    {
        if ($this->collection->isGraph()) {
            return new Edge($this->result->get($this->position), $this->collection);
        }

        return new Document($this->result->get($this->position), $this->collection);
    }
}
