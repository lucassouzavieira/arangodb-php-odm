<?php
declare(strict_types=1);

namespace ArangoDB\Cursor;

use ArangoDB\Connection\Connection;
use ArangoDB\DataStructures\ArrayList;

/**
 * Base class for Cursors
 *
 * @package ArangoDB\Cursor
 * @author Lucas S. Vieira
 */
abstract class Base
{
    /**
     * Cursor id
     *
     * @var mixed
     */
    protected $id;

    /**
     * Result data
     *
     * @var ArrayList
     */
    protected $data;

    /**
     * Connection object
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Cursor Options
     *
     * @var array
     */
    protected $options;

    /**
     * Result set
     *
     * @var array
     */
    protected $result;

    /**
     * 'hasMore' indicator
     *
     * @var boolean
     */
    protected $hasMore;

    /**
     * Current position
     *
     * @var int
     */
    protected $position;

    /**
     * Total length of current set
     *
     * @var int
     */
    protected $length;

    /**
     * Full cont of the result set
     *
     * @var int
     */
    protected $fullCount;

    /**
     * Extra data (stats)
     *
     * @var array
     */
    protected $extra;

    /**
     * Number of HTTP calls made to build the cursor
     *
     * @var int
     */
    protected $fetches = 1;

    /**
     * If the result query was served from cached results
     *
     * @var bool
     */
    protected $cached;

    /**
     * Number of documents in cursor,
     *
     * @var int
     */
    protected $count;

    /**
     * Cursor ID entry
     */
    protected const ID = 'id';

    /**
     * 'hasMore' flag
     */
    protected const HAS_MORE = 'hasMore';

    /**
     * Cursor result entry
     */
    protected const RESULT = 'result';

    /**
     * Cursor extra entry
     */
    protected const EXTRA = 'extra';

    /**
     * Cursor stats entry
     */
    protected const STATS = 'stats';

    /**
     * Cursor fullCount entry
     */
    protected const FULL_COUNT = 'fullCount';

    /**
     * Cursor cache entry
     */
    protected const CACHE = 'cache';

    /**
     * Cursor cached entry
     */
    protected const CACHED = 'cached';

    /**
     * Cursor type entry
     */
    protected const TYPE = 'objectType';

    /**
     * Cursor baseUrl entry
     */
    protected const BASE_URL = 'baseurl';

    /**
     * Cursor sanitize option
     */
    protected const SANITIZE = '_sanitize';
}
