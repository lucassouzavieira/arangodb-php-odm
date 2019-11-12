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
    const DOCUMENT = "/_api/document";
    const EDGE = "/_api/document";
    const EDGES = "/_api/edges";
    const GRAPH = "/_api/gharial";
    const INDEX = "/_api/index";
    const CURSOR = "/_api/cursor";
    const IMPORT = "/_api/import";
    const EXPORT = "/_api/export";
    const EXPLAIN = "/_api/explain";
    const BATCH = "/_api/batch";
    const QUERY = "/_api/query";
    const TRANSACTION = "/_api/transaction";
    const TRANSACTION_BEGIN = "/_api/transaction/begin";
    const AQL_USER_FUNCTION = "/_api/aqlfunction";

    const COLLECTION = "/_api/collection";
    const COLLECTION_LOAD = "/load";
    const COLLECTION_COUNT = "/count";
    const COLLECTION_UNLOAD = "/unload";
    const COLLECTION_RENAME = "/rename";
    const COLLECTION_ROTATE = "/rotate";
    const COLLECTION_CHECKSUM = "/checksum";
    const COLLECTION_REVISION = "/revision";
    const COLLECTION_TRUNCATE = "/truncate";
    const COLLECTION_PROPERTIES = "/properties";
    const COLLECTION_RECALCULATE_COUNT = "/recalculateCount";

    const USER = "/_api/user";
    const TRAVERSAL = "/_api/traversal";
    const ENDPOINT = "/_api/endpoint";
    const DATABASE = "/_api/database";
    const CURRENT_DATABASE = "/_api/database/current";
    const USER_DATABASES = "/_api/database/user";
    const QUERY_CACHE = "/_api/query-cache";
    const UPLOAD = "/_api/upload";

    const PART_VERTEX = "vertex";
    const PART_EDGE = "vertex";

    const LOOKUP_BY_KEYS = "/_api/simple/lookup-by_keys";
    const ALL = "/_api/simple/all";
    const ALL_KEYS = "/_api/simple/all";
    const ANY = "/_api/simple/any";
    const FULLTEXT = "/_api/simple/fulltext";
    const REMOVE_BY_KEYS = "/_api/simple/remove-by-keys";

    const EXAMPLE = "/_api/simple/by-example";
    const FIRST_EXAMPLE = "/_api/simple/first-example";
    const UPDATE_BY_EXAMPLE = "/_api/simple/update-by-example";
    const REMOVE_BY_EXAMPLE = "/_api/simple/remove-by-example";
    const REPLACE_BY_EXAMPLE = "/_api/simple/replace-by-example";

    const ADMIN_TASKS = "/_api/tasks";
    const ADMIN_VERSION = "/_api/version";
    const ADMIN_ENGINE = "/_api/engine";
    const ADMIN_SERVER_ROLE = "/_admin/server/role";
    const ADMIN_SERVER_AVAILABILITY = "/_admin/server/availability";
    const ADMIN_TIME = "/_admin/time";
    const ADMIN_LOG = "/_admin/log";
    const ADMIN_FLUSH_WAL = "/_admin/wal/flush";
    const ADMIN_WAL_PROPERTIES = "_admin/wal/properties";
    const ADMIN_WAL_TRANSACTIONS = "_admin/wal/transactions";
    const ADMIN_LOG_LEVEL = "/_admin/log/level";
    const ADMIN_ROUTING_RELOAD = "/_admin/routing/reload";
    const ADMIN_STATISTICS = "/_admin/statistics";
    const ADMIN_STATISTICS_DESCRIPTION = "/_admin/statistics-description";
    const FOXX = "/_api/foxx";
    const FOXX_SERVICE = "/_api/foxx/service";

    const DB = "/_db/";
    const AUTH_BASE = "/_open/auth";
    const JWT_AUTH_BASE = "/_open/auth";


    /**
     * Add an URI param to an URI
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
     * Add an URI query
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
     * @param string $apiEndpoint Base Api endpoint (One of Api class constants).
     *
     * @return string The modified database URI.
     */
    public static function buildDatabaseUri(string $baseUri, string $database, string $apiEndpoint = ''): string
    {
        return sprintf("%s%s%s", $baseUri . Api::DB, $database, $apiEndpoint);
    }

    /**
     * Builds URIs for access some special endpoints on Arango HTTP Interface
     *
     * @param string $baseUri Base URI to add a parameter.
     * @param string $endpoint One of Api class constants.
     *
     * @return string The modified system URI.
     */
    public static function buildSystemUri(string $baseUri, string $endpoint): string
    {
        return sprintf("%s%s", $baseUri, $endpoint);
    }
}
