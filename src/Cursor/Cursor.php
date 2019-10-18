<?php
declare(strict_types=1);

namespace ArangoDB\Cursor;

use ArangoDB\Connection\Connection;
use ArangoDB\DataStructures\ArrayList;

/**
 * Represents an ArangoDB cursor
 *
 * @package ArangoDB\Cursor
 * @author Lucas S. Vieira
 */
class Cursor extends Base # implements \Iterator
{
    /**
     * Default options query for the cursor
     * 'count', 'batchSize' and 'options' are, by default, leaved for the server defaults
     *
     * @var array
     */
    protected $defaultOptions = [
        'cache' => false,
        'memoryLimit' => 0,
        'ttl' => 30,
    ];

    /**
     * Cursor constructor.
     *
     * @param Connection $connection
     * @param array $options
     */
    public function __construct(Connection $connection, array $options = [])
    {
        $this->options = array_merge($this->defaultOptions, $options);
        $this->connection = $connection;
    }
}
