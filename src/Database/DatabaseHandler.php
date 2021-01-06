<?php
declare(strict_types=1);

namespace ArangoDB\Database;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use ArangoDB\DataStructures\ArrayList;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Connection\ManagesConnection;
use ArangoDB\Exceptions\Database\DatabaseException;

/**
 * Manages databases on Arango server
 *
 * @package ArangoDB\Database
 * @author Lucas S. Vieira
 */
abstract class DatabaseHandler extends ManagesConnection
{
    /**
     * Creates a new database on server
     *
     * @param Connection $connection Connection object to be used
     * @param string $database Database name
     *
     * @return bool True if database was successfully created on server. Throw an exception otherwise.
     *
     * @throws GuzzleException|DatabaseException
     */
    public static function create(Connection $connection, string $database): bool
    {
        try {
            $db = [
                'name' => $database
            ];

            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::DATABASE);
            $response = $connection->post($uri, $db);
            $data = json_decode((string)$response->getBody(), true);
            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Drops a database from server
     *
     * @param Connection $connection Connection object to be used
     * @param string $database Database name
     *
     * @return bool True if database was successfully dropped on server. Throw an exception otherwise.
     *
     * @throws DatabaseException|GuzzleException
     */
    public static function drop(Connection $connection, string $database): bool
    {
        try {
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::DATABASE);
            $uri = sprintf("%s/%s", $uri, $database);
            $response = $connection->delete($uri);
            $data = json_decode((string)$response->getBody(), true);
            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);

            // DatabaseHandler not found on server.
            if ($exception->getResponse()->getStatusCode() == 404) {
                return false;
            }

            throw $databaseException;
        }
    }

    /**
     * Lists databases that exists on server
     *
     * @param Connection $connection Connection object to be used
     *
     * @return ArrayList A list with all databases on server.
     *
     * @throws GuzzleException|DatabaseException
     */
    public static function list(Connection $connection): ArrayList
    {
        try {
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::DATABASE);
            $response = $connection->get($uri);
            $data = json_decode((string)$response->getBody(), true);

            return new ArrayList($data['result']);
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Lists the databases that current user has access
     *
     * @param Connection $connection Connection object to be used
     *
     * @return ArrayList A list with all databases on server that current user has access.
     *
     * @throws GuzzleException|DatabaseException
     */
    public static function userDatabases(Connection $connection): ArrayList
    {
        try {
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::USER_DATABASES);
            $response = $connection->get($uri);
            $data = json_decode((string)$response->getBody(), true);

            return new ArrayList($data['result']);
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }

    /**
     * Returns information about the current database
     *
     * @param Connection $connection Connection object to be used
     *
     * @return array An array with information about database.
     *
     * @throws GuzzleException|DatabaseException
     * @see https://www.arangodb.com/docs/stable/http/database-database-management.html#information-of-the-database
     */
    public static function current(Connection $connection): array
    {
        try {
            $uri = Api::buildSystemUri($connection->getBaseUri(), Api::CURRENT_DATABASE);
            $response = $connection->get($uri);
            $data = json_decode((string)$response->getBody(), true);

            return $data['result'];
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $databaseException = new DatabaseException($response['errorMessage'], $exception, $response['errorNum']);
            throw $databaseException;
        }
    }
}
