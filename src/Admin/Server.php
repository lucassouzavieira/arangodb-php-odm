<?php
declare(strict_types=1);

namespace ArangoDB\Admin;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use ArangoDB\Exceptions\ServerException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Manages some server admin features.
 *
 * @package ArangoDB\Admin
 * @author  Lucas S. Vieira
 */
abstract class Server
{
    /**
     * Returns the Arango server version.
     *
     * @param Connection $connection Connection object to use.
     *
     * @return string The server version string. The string has the format “major.minor.sub”.<br>
     * Major and minor will be numeric, and sub may contain a number or a textual version.
     *
     * @throws ServerException|GuzzleException
     */
    public static function version(Connection $connection): string
    {
        try {
            $response = $connection->get(sprintf(Api::ADMIN_VERSION));
            $data = json_decode((string)$response->getBody(), true);
            return $data['version'];
        } catch (ClientException $exception) {
            // Unknown error.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $serverException = new ServerException($response['errorMessage'], $exception, $response['errorNum']);
            throw $serverException;
        }
    }

    /**
     * Returns the storage engine the server is configured to use.
     * (mmfiles or rocksdb)
     *
     * @param  Connection $connection Connection object to use.
     * @return string Engine name. Will be <b>mmfiles</b> or <b>rocksdb</b>
     *
     * @throws GuzzleException|ServerException
     * @see    https://www.arangodb.com/docs/stable/http/miscellaneous-functions.html#return-server-database-engine-type
     */
    public static function engine(Connection $connection): string
    {
        try {
            $response = $connection->get(sprintf(Api::ADMIN_ENGINE));
            $data = json_decode((string)$response->getBody(), true);
            return $data['name'];
        } catch (\Exception $exception) {
            // Unknown error.
            $serverException = new ServerException($exception->getMessage(), $exception, $exception->getCode());
            throw $serverException;
        }
    }

    /**
     * Returns the role of a server in a cluster.
     *
     * @param Connection $connection Connection object to use.
     *
     * @return string The role of server in a cluster. Possible return values for role are: <br>
     * <b>single</b>: for standalone servers. <br>
     * <b>coordinator</b>: if server is a coordinator in a cluster. <br>
     * <b>primary</b>: if the server is a DBServer in a cluster. <br>
     * <b>secondary</b>: a not used role. <br>
     * <b>agent</b>: if the server is an Agency node in a cluster. <br>
     * <b>undefined</b>: if the role cannot be determined. <br>
     *
     * @throws GuzzleException|ServerException
     */
    public static function role(Connection $connection): string
    {
        try {
            $response = $connection->get(sprintf(Api::ADMIN_SERVER_ROLE));
            $data = json_decode((string)$response->getBody(), true);
            return strtolower($data['role']);
        } catch (\Exception $exception) {
            // Unknown error.
            $serverException = new ServerException($exception->getMessage(), $exception, $exception->getCode());
            throw $serverException;
        }
    }

    /**
     * Checks if the Arango server is available for arbitrary operations.<br>
     * (e.g Is not set to read-only mode and isn't a follower on failover setups)<br>
     * If server during startup or during shutdown returns false.
     *
     * @param Connection $connection Connection object to use.
     *
     * @return boolean True if server is available. False if not.
     *
     * @throws ServerException|GuzzleException
     */
    public static function isAvailable(Connection $connection): bool
    {
        try {
            $connection->get(sprintf(Api::ADMIN_SERVER_AVAILABILITY));
            return true;
        } catch (BadResponseException $exception) {
            if ($exception->getResponse()->getStatusCode() === 503) {
                // Server unavailable
                return false;
            }

            // Unknown error.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $serverException = new ServerException($response['errorMessage'], $exception, $response['errorNum']);
            throw $serverException;
        }
    }

    /**
     * Returns the server's current log level settings.
     *
     * @param Connection $connection Connection object to use.
     *
     * @return array An array with the log topics being the object keys, and the log levels being the object values.
     *
     * @throws ServerException|GuzzleException
     */
    public static function logLevel(Connection $connection): array
    {
        try {
            $response = $connection->get(sprintf(Api::ADMIN_LOG_LEVEL));
            $data = json_decode((string)$response->getBody(), true);
            return $data;
        } catch (BadResponseException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $serverException = new ServerException($response['errorMessage'], $exception, $response['errorNum']);
            throw $serverException;
        }
    }
}
