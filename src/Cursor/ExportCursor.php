<?php
declare(strict_types=1);

namespace ArangoDB\Cursor;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use ArangoDB\DataStructures\ArrayList;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Exceptions\DatabaseException;
use ArangoDB\Cursor\Exceptions\CursorException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Represents an ArangoDB export cursor
 *
 * @package ArangoDB\Cursor
 * @author Lucas S. Vieira
 */
class ExportCursor extends CollectionCursor
{
    /**
     * Default options query for the cursor
     * 'restrict', 'batchSize' and 'options' are,
     * by default, leaved for the server defaults
     *
     * @var array
     */
    protected $defaultOptions = [
        'count' => true,
        'flush' => true,
        'flushWait' => 10,
        'limit' => 0,
        'ttl' => 60
    ];

    /**
     * ExportCursor constructor.
     *
     * @param Connection $connection Connection object to use
     * @param string $collection Collection to export
     * @param array $options Options for cursor
     *
     * @throws CursorException|GuzzleException|DatabaseException|InvalidParameterException|MissingParameterException
     */
    public function __construct(Connection $connection, string $collection, array $options = [])
    {
        if (!$connection->getDatabase()->hasCollection($collection)) {
            throw new DatabaseException("Collection ($collection) doesn't exists.");
        }

        $this->uri = Api::addQuery(Api::EXPORT, ['collection' => $collection]);
        $this->connection = $connection;
        $this->result = new ArrayList();
        $this->options = array_merge($this->defaultOptions, $options);
        $this->create();
    }

    /**
     * Return body for creating cursor
     *
     * @return array
     */
    protected function getBody(): array
    {
        return array_merge($this->options);
    }
}
