<?php
declare(strict_types=1);

namespace ArangoDB\AQL;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\AQL\Functions\AQLFunction;
use ArangoDB\Exceptions\ServerException;
use GuzzleHttp\Exception\ClientException;
use ArangoDB\AQL\Exceptions\AQLException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\AQL\Contracts\StatementInterface;

/**
 * Manages some AQL features
 *
 * @package ArangoDB\AQL
 * @author Lucas S. Vieira
 */
abstract class AQL
{
    /**
     * Validates a given AQL statement
     *
     * @param StatementInterface $statement
     * @param Connection $connection
     * @return bool
     *
     * @throws AQLException|GuzzleException
     */
    public static function validateQuery(StatementInterface $statement, Connection $connection): bool
    {
        try {
            $response = $connection->post(sprintf(Api::QUERY), ['query' => $statement->getQuery()]);
            $data = json_decode((string)$response->getBody(), true);
            return !$data['error'];
        } catch (ClientException $exception) {
            // Invalid query
            if ($exception->getResponse()->getStatusCode() === 400) {
                return false;
            }

            // Unknown error.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $aqlException = new AQLException($response['errorMessage'], $exception, $response['errorNum']);
            throw $aqlException;
        }
    }

    /**
     * Returns all tasks of server information.
     *
     * @param Connection $connection
     * @param string $namespace
     * @return ArrayList[AQLFunction] ArrayList with all AQL functions from server
     *
     * @throws ServerException|GuzzleException
     */
    public static function functions(Connection $connection, string $namespace = ""): ArrayList
    {
        try {
            $uri = !strlen($namespace) ? Api::AQL_USER_FUNCTION : Api::addUriParam(Api::AQL_USER_FUNCTION, $namespace);

            $functions = new ArrayList();
            $response = $connection->get($uri);
            $data = json_decode((string)$response->getBody(), true);
            foreach ($data['result'] as $aqlFunction) {
                $functions->push(new AQLFunction($aqlFunction['name'], $aqlFunction['code'], $connection, $aqlFunction['isDeterministic'], false));
            }

            return $functions;
        } catch (ClientException $exception) {
            // Unknown error.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $serverException = new ServerException($response['errorMessage'], $exception, $response['errorNum']);
            throw $serverException;
        }

    }
}
