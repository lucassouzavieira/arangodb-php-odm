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
    const COLLECTION = "/_api/collection";
    const INDEX = "/_api/index";
    const CURSOR = "/_api/cursor";
    const IMPORT = "/_api/import";
    const EXPORT = "/_api/export";
    const EXPLAIN = "/_api/explain";
    const BATCH = "/_api/batch";
    const QUERY = "/_api/query";
    const TRANSACTION = "/_api/transaction";
    const AQL_USER_FUNCTION = "/_api/aqlfunction";
    const COLLECTION_PROPERTIES = "/properties";

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

    const ADMIN_VERSION = "/_admin/version";
    const ADMIN_SERVER_ROLE = "/_admin/server/role";
    const ADMIN_TIME = "/_admin/time";
    const ADMIN_LOG = "/_admin/log";
    const ADMIN_ROUTING_RELOAD = "/_admin/routing/reload";
    const ADMIN_STATISTICS = "/_admin/statistics";
    const ADMIN_STATISTICS_DESCRIPTION = "/_admin/statistics-description";
    const FOXX_INSTALL = "/_admin/foxx/install";
    const FOXX_UNINSTALL = "/_admin/foxx/uninstall";

    const DB = "/_db/";
    const AUTH_BASE = "/_open/auth";
    const JWT_AUTH_BASE = "/_open/auth";

    /**
     * Builds URIs for access to Arango HTTP Interface
     *
     * @param string $baseUri
     * @param string $database
     * @param string $apiEndpoint
     * @return string
     */
    public static function buildDatabaseUri(string $baseUri, string $database, string $apiEndpoint = '')
    {
        return sprintf("%s%s%s", $baseUri . Api::DB, $database, $apiEndpoint);
    }

    /**
     * Builds URIs for access some special endpoints on Arango HTTP Interface
     *
     * @param string $baseUri
     * @param string $endpoint
     * @return string
     */
    public static function buildSystemUri(string $baseUri, string $endpoint)
    {
        return sprintf("%s%s", $baseUri, $endpoint);
    }

    /**
     * Add query
     *
     * @param string $baseUri
     * @param array $data
     * @return string
     */
    public static function addQuery(string $baseUri, array $data = [])
    {
        return sprintf("%s?%s", $baseUri, http_build_query($data));
    }
}
