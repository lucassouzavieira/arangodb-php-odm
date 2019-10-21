<?php
declare(strict_types=1);

namespace ArangoDB\Admin;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use ArangoDB\Exceptions\ServerException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

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
}