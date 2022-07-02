<?php
declare(strict_types=1);

namespace ArangoDB\Admin\Task;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use ArangoDB\Exceptions\ServerException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ClientException;
use ArangoDB\Entity\Contracts\EntityInterface;
use ArangoDB\Validation\Admin\Task\TaskValidator;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Represents an user defined task on server.
 *
 * @package ArangoDB\Admin\Task
 * @author  Lucas S. Vieira
 */
class Task implements EntityInterface
{
    /**
     * Task id.
     *
     * @var string
     */
    protected $id;

    /**
     * Task type.
     *
     * @var string
     */
    protected $type = 'unknown';

    /**
     * Attributes of task.
     *
     * @var array
     */
    protected $attributes;

    /**
     * Connection object.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Default options.
     *
     * @var array
     */
    protected $defaultOptions = [
        'offset' => 30,
        'period' => 30,
        'params' => []
    ];

    /**
     * If the entity is not an representation of a existing task on server,
     * this property is true.
     *
     * @var bool
     */
    protected $isNew = true;

    /**
     * Task constructor.
     *
     * @param string $name Task name.
     * @param string $command Task command.
     * @param Connection|null $connection Connection to use.
     * @param array $options Additional options for task.
     *
     * @throws InvalidParameterException|MissingParameterException
     */
    public function __construct(string $name, string $command, Connection $connection = null, array $options = [])
    {
        $attributes = array_merge($this->defaultOptions, ['name' => $name, 'command' => $command], $options);
        $validator = new TaskValidator($attributes);
        $validator->validate();

        if (isset($attributes['id'])) {
            $this->id = $attributes['id'];
            unset($attributes['id']);
            $this->isNew = false;
        }

        if (isset($attributes['type'])) {
            $this->type = $attributes['type'];
        }

        $this->attributes = $attributes;
        $this->connection = $connection;
    }

    /**
     * If the task object is a new created task (and not exists on server) <br>
     * or if it is a representation of an existing one.
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * Return the Task id.
     *
     * @return string|null Task id, if exists or null if the task is a new one.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return the task type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the command.
     *
     * @return string
     */
    public function getCommand(): string
    {
        return $this->attributes['command'];
    }

    /**
     * Returns a array representation of task.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_merge($this->attributes, ['id' => $this->id]);
    }

    /**
     * If this task has a connection set or not.
     *
     * @return bool True of has a connection object. False otherwise.
     */
    public function hasConnection(): bool
    {
        return !($this->connection === null);
    }

    /**
     * Sets a custom id for task.
     *
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * Sets the command to task.
     *
     * @param string $command
     */
    public function setCommand(string $command): void
    {
        $this->attributes['command'] = $command;
    }


    /**
     * Sets a connection to use.
     *
     * @param Connection $connection Connection object to use.
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Saves this task on server, if possible
     *
     * @return bool True if operation was successful, false otherwise
     *
     * @throws ServerException|GuzzleException
     */
    public function save(): bool
    {
        try {
            $method = 'post';
            $uri = Api::ADMIN_TASKS;

            // Create a task with custom id.
            if ($this->isNew() && $this->getId()) {
                $uri = Api::addUriParam(Api::ADMIN_TASKS, $this->getId());
                $method = 'put';
            }

            if ($this->hasConnection()) {
                $response = $this->connection->$method($uri, $this->attributes);
                $data = json_decode((string)$response->getBody(), true);
                $this->id = $data['id'];
                $this->type = $data['type'];
                unset($data['id'], $data['type']);
                $this->attributes = $data;
                $this->isNew = false;
                return true;
            }

            return false;
        } catch (ClientException $exception) {
            // Unknown error.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $serverException = new ServerException($response['errorMessage'], $exception, $response['errorNum']);
            throw $serverException;
        }
    }

    /**
     * Removes a task from server, if possible.
     *
     * @return bool True if operation was successful, false otherwise
     *
     * @throws ServerException|GuzzleException
     */
    public function delete(): bool
    {
        try {
            if (!$this->isNew() && $this->hasConnection()) {
                $this->connection->delete(Api::addUriParam(Api::ADMIN_TASKS, $this->getId()));
                return true;
            }

            return false;
        } catch (ClientException $exception) {
            // Task not found
            if ($exception->getResponse()->getStatusCode() === 404) {
                return false;
            }

            // Unknown error.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $serverException = new ServerException($response['errorMessage'], $exception, $response['errorNum']);
            throw $serverException;
        }
    }

    /**
     * Return a JSON representation of Task object.
     *
     * @return array|mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
