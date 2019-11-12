<?php
declare(strict_types=1);

namespace ArangoDB\Batch;

use ArangoDB\Cursor\ExportCursor;
use ArangoDB\Connection\Connection;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Exceptions\DatabaseException;
use ArangoDB\Cursor\Contracts\CursorInterface;
use ArangoDB\Cursor\Exceptions\CursorException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Allow export data from a collection.
 *
 * @package ArangoDB\Batch
 * @author Lucas S. Vieira
 */
abstract class Export
{
    /**
     * Default export options.
     *
     * @var array
     */
    protected static $defaultExportOptions = [
        'flush' => true,
        'flushWait' => 10,
        'count' => true,
        'limit' => 0,
        'ttl' => 60,
    ];

    /**
     * Exports a collection.
     *
     * @param Connection $connection Connection object to use.
     * @param string $collection Name of collection to export.
     * @param array $options Export options to use. If not set, a default set of options will be used.
     *
     * @return CursorInterface A ExportCursor to be used for export.
     *
     * @throws CursorException|GuzzleException|InvalidParameterException|MissingParameterException|DatabaseException
     * @see https://www.arangodb.com/docs/stable/http/export.html#create-export-cursor
     */
    public static function collection(Connection $connection, string $collection, array $options = []): CursorInterface
    {
        return new ExportCursor($connection, $collection, array_merge(self::$defaultExportOptions, $options));
    }
}
