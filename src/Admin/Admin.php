<?php
declare(strict_types=1);

namespace ArangoDB\Admin;

use ArangoDB\Http\Api;
use ArangoDB\Admin\Task\Task;
use ArangoDB\Connection\Connection;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Exceptions\ServerException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

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
            $response = $connection->get(Api::ADMIN_STATISTICS);
            $data = json_decode((string)$response->getBody(), true);
            return $data;
        } catch (ClientException $exception) {
            // Unknown error.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $serverException = new ServerException($response['errorMessage'], $exception, $response['errorNum']);
            throw $serverException;
        }
    }

    /**
     * Returns all tasks of server information.
     *
     * @param Connection $connection
     * @return ArrayList ArrayList with all tasks from  server
     * @throws ServerException|GuzzleException|InvalidParameterException|MissingParameterException
     */
    public static function tasks(Connection $connection): ArrayList
    {
        try {
            $tasks = new ArrayList();
            $response = $connection->get(Api::ADMIN_TASKS);
            $data = json_decode((string)$response->getBody(), true);
            foreach ($data as $taskData) {
                $name = $taskData['name'];
                $command = $taskData['command'];
                $tasks->push(new Task($name, $command, $connection, $taskData));
            }

            return $tasks;
        } catch (ClientException $exception) {
            // Unknown error.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $serverException = new ServerException($response['errorMessage'], $exception, $response['errorNum']);
            throw $serverException;
        }
    }
}
