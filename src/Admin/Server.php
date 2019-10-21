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
 * Manages some server admin features
 *
 * @package ArangoDB\Admin
 * @author Lucas S. Vieira
 */
abstract class Server
{
    /**
     * Returns the Arango server version
     *
     * @param Connection $connection
     * @return string
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
     * Checks if the Arango server is available for arbitrary operations
     * (e.g Is not set to read-only mode and isn't a follower on failover setups)
     * If server during startup or during shutdown returns false
     *
     * @param Connection $connection
     * @return boolean True if server is available. False if not.
     * @throws ServerException|GuzzleException
     */
    public static function isAvailable(Connection $connection): bool
    {
        try {
            $response = $connection->get(sprintf(Api::ADMIN_SERVER_AVAILABILITY));
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
}
