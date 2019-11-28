<?php
declare(strict_types=1);

namespace ArangoDB\View;

/**
 * Represents a View on ArangoDB server.
 *
 * @package ArangoDB\View
 * @author Lucas S. Vieira
 */
class View
{
    /**
     * View ID.
     *
     * @var string
     */
    protected $id;

    /**
     * Globally unique ID.
     *
     * @var string
     */
    protected $globallyUniqueId;

    /**
     * The name of the View.
     *
     * @var string
     */
    protected $name;

    /**
     * The type of View
     *
     * @var string
     */
    protected $type;

    /**
     * The view links
     *
     * @var mixed
     * @see https://www.arangodb.com/docs/stable/arangosearch-views.html#link-properties
     */
    protected $links;

    /**
     * Primary sort order definitions
     *
     * @var mixed
     * @see https://www.arangodb.com/docs/stable/arangosearch-views.html#primary-sort-order
     */
    protected $primarySort;

    /**
     * Wait at least this many milliseconds between committing View data store
     * changes and making documents visible to queries.
     *
     * @var int
     */
    protected $commitInterval = 1000;

    /**
     * Consolidation policy
     *
     * @var mixed
     * @see https://www.arangodb.com/docs/stable/arangosearch-views.html#view-properties
     */
    protected $consolidationPolicy = null;

    /**
     * ait at least this many milliseconds between applying 'consolidationPolicy' to
     * consolidate View data store and possibly release space on the filesystem
     * (default: 10000, to disable use: 0).
     *
     * @var int
     * @see https://www.arangodb.com/docs/stable/arangosearch-views.html#view-properties
     */
    protected $consolidationInterval = 10000;

    /**
     * Wait at least this many commits between removing unused files in the
     * ArangoSearch data directory
     *
     * @var int
     * @see https://www.arangodb.com/docs/stable/arangosearch-views.html#view-properties
     */
    protected $cleanupIntervalStep = 2;

    /**
     * Maximum number of writers (segments) cached in the pool.
     *
     * @var int
     * @see https://www.arangodb.com/docs/stable/arangosearch-views.html#view-properties
     */
    protected $writeBufferIdle = 64;

    /**
     * Maximum memory byte size per writer (segment) before a writer (segment) flush is triggered
     *
     * @var int
     * @see https://www.arangodb.com/docs/stable/arangosearch-views.html#view-properties
     */
    protected $writeBufferSizeMax = 33554432;

    /**
     *  Maximum number of concurrent active writers (segments) that perform a
     * transaction. Other writers (segments) wait till current active writers
     * (segments) finish.
     *
     * @var int
     * @see https://www.arangodb.com/docs/stable/arangosearch-views.html#view-properties
     */
    protected $writeBufferActive = 0;
}
