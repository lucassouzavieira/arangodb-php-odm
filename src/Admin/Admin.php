<?php
declare(strict_types=1);

namespace ArangoDB\Admin;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use ArangoDB\Exceptions\ServerException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Manages some Admin features
 *
 * @package ArangoDB\Admin
 * @author Lucas S. Vieira
 */
abstract class Admin
{
    /**
     * Returns the statistics information.
     *
     * @param Connection $connection
     * @return array Array with statistics information about server
     * @throws ServerException|GuzzleException
     */
    public static function statistics(Connection $connection)
    {
        try {
            $response = $connection->get(sprintf(Api::ADMIN_STATISTICS));
            $data = json_decode((string)$response->getBody(), true);
            return $data;
        } catch (ClientException $exception) {
            // Unknown error.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $serverException = new ServerException($response['errorMessage'], $exception, $response['errorNum']);
            throw $serverException;
        }

    }
}
