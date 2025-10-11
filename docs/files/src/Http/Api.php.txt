<?php

namespace ArangoDB\Http;

/**
 * API Helper to Arango HTTP Interface
 *
 * @package ArangoDB\Http
 * @copyright 2018 Lucas S. Vieira
 */
abstract class Api
{
    public const DOCUMENT = "/_api/document";
    public const EDGE = "/_api/document";
    public const EDGES = "/_api/edges";
    public const GRAPH = "/_api/gharial";
    public const INDEX = "/_api/index";
    public const CURSOR = "/_api/cursor";
    public const IMPORT = "/_api/import";
    public const EXPLAIN = "/_api/explain";
    public const BATCH = "/_api/batch";
    public const QUERY = "/_api/query";
    public const TRANSACTION = "/_api/transaction";
    public const TRANSACTION_BEGIN = "/_api/transaction/begin";
    public const AQL_USER_FUNCTION = "/_api/aqlfunction";

    public const COLLECTION = "/_api/collection";
    public const COLLECTION_LOAD = "/load";
    public const COLLECTION_COUNT = "/count";
    public const COLLECTION_RENAME = "/rename";
    public const COLLECTION_ROTATE = "/rotate";
    public const COLLECTION_CHECKSUM = "/checksum";
    public const COLLECTION_REVISION = "/revision";
    public const COLLECTION_TRUNCATE = "/truncate";
    public const COLLECTION_PROPERTIES = "/properties";
    public const COLLECTION_RECALCULATE_COUNT = "/recalculateCount";

    public const USER = "/_api/user";
    public const TRAVERSAL = "/_api/traversal";
    public const ENDPOINT = "/_api/endpoint";
    public const DATABASE = "/_api/database";
    public const CURRENT_DATABASE = "/_api/database/current";
    public const USER_DATABASES = "/_api/database/user";
    public const QUERY_CACHE = "/_api/query-cache";
    public const UPLOAD = "/_api/upload";

    public const PART_VERTEX = "vertex";
    public const PART_EDGE = "vertex";

    public const LOOKUP_BY_KEYS = "/_api/simple/lookup-by_keys";
    public const ALL = "/_api/simple/all";
    public const ALL_KEYS = "/_api/simple/all";
    public const ANY = "/_api/simple/any";
    public const FULLTEXT = "/_api/simple/fulltext";
    public const REMOVE_BY_KEYS = "/_api/simple/remove-by-keys";

    public const EXAMPLE = "/_api/simple/by-example";
    public const FIRST_EXAMPLE = "/_api/simple/first-example";
    public const UPDATE_BY_EXAMPLE = "/_api/simple/update-by-example";
    public const REMOVE_BY_EXAMPLE = "/_api/simple/remove-by-example";
    public const REPLACE_BY_EXAMPLE = "/_api/simple/replace-by-example";

    public const ADMIN_TASKS = "/_api/tasks";
    public const ADMIN_VERSION = "/_api/version";
    public const ADMIN_ENGINE = "/_api/engine";
    public const ADMIN_SERVER_ROLE = "/_admin/server/role";
    public const ADMIN_SERVER_AVAILABILITY = "/_admin/server/availability";
    public const ADMIN_TIME = "/_admin/time";
    public const ADMIN_LOG = "/_admin/log";
    public const ADMIN_FLUSH_WAL = "/_admin/wal/flush";
    public const ADMIN_WAL_PROPERTIES = "_admin/wal/properties";
    public const ADMIN_WAL_TRANSACTIONS = "_admin/wal/transactions";
    public const ADMIN_LOG_LEVEL = "/_admin/log/level";
    public const ADMIN_ROUTING_RELOAD = "/_admin/routing/reload";
    public const ADMIN_STATISTICS = "/_admin/statistics";
    public const ADMIN_STATISTICS_DESCRIPTION = "/_admin/statistics-description";
    public const FOXX = "/_api/foxx";
    public const FOXX_SERVICE = "/_api/foxx/service";

    public const DB = "/_db/";
    public const AUTH_BASE = "/_open/auth";
    public const JWT_AUTH_BASE = "/_open/auth";

    /**
     * Add a param to the URI
     *
     * @param string $baseUri Base URI to add a parameter.
     * @param string|integer $param Parameter value.
     *
     * @return string The modified URI.
     */
    public static function addUriParam(string $baseUri, $param): string
    {
        return sprintf("%s/%s", $baseUri, $param);
    }

    /**
     * Add a URI query
     *
     * @param string $baseUri Base URI to add a query.
     * @param array $data Query data.
     *
     * @return string The modified URI with the Query.
     */
    public static function addQuery(string $baseUri, array $data = []): string
    {
        return sprintf("%s?%s", $baseUri, http_build_query($data));
    }

    /**
     * Builds URIs for access to Arango HTTP Interface
     *
     * @param string $baseUri Base URI to add a parameter.
     * @param string $database Database name.
     * @param string $apiEndpoint Base Api endpoint (One of Api class public constants).
     *
     * @return string The modified database URI.
     */
    public static function buildDatabaseUri(
        string $baseUri,
        string $database,
        string $apiEndpoint = "",
    ): string {
        return sprintf("%s%s%s", $baseUri . Api::DB, $database, $apiEndpoint);
    }

    /**
     * Builds URIs for access some special endpoints on Arango HTTP Interface
     *
     * @param string $baseUri Base URI to add a parameter.
     * @param string $endpoint One of Api class public constants.
     *
     * @return string The modified system URI.
     */
    public static function buildSystemUri(
        string $baseUri,
        string $endpoint,
    ): string {
        return sprintf("%s%s", $baseUri, $endpoint);
    }
}
