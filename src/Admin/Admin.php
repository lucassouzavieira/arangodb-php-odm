<?php
declare(strict_types=1);

namespace ArangoDB\Admin;

use ArangoDB\Http\Api;
use ArangoDB\Admin\Task\Task;
use ArangoDB\Connection\Connection;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Exceptions\ServerException;
use GuzzleHttp\Exception\BadResponseException;
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
     *
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
     * Returns the system time.
     *
     * @param Connection $connection
     * @return int Unix timestamp from server
     *
     * @throws ServerException|GuzzleException
     */
    public static function time(Connection $connection): float
    {
        try {
            $response = $connection->get(Api::ADMIN_TIME);
            $data = json_decode((string)$response->getBody(), true);
            return $data['time'];
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
     * @return ArrayList[Task] ArrayList with all tasks from  server
     *
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

    /**
     * Flushes the write-ahead log. By flushing the currently active write-ahead
     * logfile, the data in it can be transferred to collection journals and
     * datafiles. This is useful to ensure that all data for a collection is
     * present in the collection journals and datafiles, for example, when dumping
     * the data of a collection.
     *
     * @param Connection $connection
     * @param bool $waitForSync If true, the operation should block until the not-yet synchronized data in the write-ahead log was synchronized to disk.
     * @param bool $waitForCollector If true, the operation should block until the data in the flushed log has been collected by the write-ahead log garbage collector.
     * @return bool True if operation was successful. False otherwise.
     *
     * @throws ServerException|GuzzleException
     */
    public static function flushWal(Connection $connection, bool $waitForSync = true, bool $waitForCollector = true): bool
    {
        try {
            $options = [
                'waitForSync' => $waitForSync,
                'waitForCollector' => $waitForCollector
            ];

            $connection->put(Api::ADMIN_FLUSH_WAL, $options);
            return true;
        } catch (ClientException $exception) {
            // Unknown error.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $serverException = new ServerException($response['errorMessage'], $exception, $response['errorNum']);
            throw $serverException;
        }
    }

    /**
     * Retrieves the configuration of the write-ahead log.
     * The result is a array with the following keys:
     * 'allowOversizeEntries': whether or not operations that are bigger than a single logfile can be executed and stored
     * 'logfileSize': the size of each write-ahead logfile
     * 'historicLogfiles': the maximum number of historic logfiles to keep
     * 'reserveLogfiles': the maximum number of reserve logfiles that ArangoDB allocates in the background
     * 'syncInterval': the interval for automatic synchronization of not-yet synchronized write-ahead log data (in milliseconds)
     * 'throttleWait': the maximum wait time that operations will wait before they get aborted if case of write-throttling (in milliseconds)
     * 'throttleWhenPending': the number of unprocessed garbage-collection operations that, when reached, will activate write-throttling.
     * A value of 0 means that write-throttling will not be triggered.
     *
     * @param Connection $connection
     * @return array
     *
     * @throws ServerException|GuzzleException
     */
    public static function walProperties(Connection $connection): array
    {
        try {
            $response = $connection->get(Api::ADMIN_WAL_PROPERTIES);
            $data = json_decode((string)$response->getBody(), true);
            return $data;
        } catch (BadResponseException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $message = isset($response['errorMessage']) ? $response['errorMessage'] : "Unknown error";
            $code = isset($response['errorNum']) ? $response['errorNum'] : $exception->getResponse()->getStatusCode();

            // Not implemented on server.
            if ($exception->getResponse()->getStatusCode() === 501) {
                $message = "Not Implemented";
            }

            $serverException = new ServerException($message, $exception, $code);
            throw $serverException;
        }
    }
}
