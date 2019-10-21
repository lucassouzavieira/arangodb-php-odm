<?php
declare(strict_types=1);

namespace ArangoDB\AQL;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use GuzzleHttp\Exception\ClientException;
use ArangoDB\AQL\Exceptions\AQLException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\AQL\Contracts\StatementInterface;

/**
 * Represents an prepared AQL Statement
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
     * @throws AQLException|GuzzleException
     */
    public static function validateQuery(StatementInterface $statement, Connection $connection): bool
    {
        try {
            $response = $connection->post(sprintf(Api::QUERY), ['query' => $statement->toAql()]);
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
}
