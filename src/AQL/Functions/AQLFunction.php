<?php
declare(strict_types=1);

namespace ArangoDB\AQL\Functions;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use ArangoDB\Exceptions\ServerException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Entity\Contracts\EntityInterface;

/**
 * Represents a user defined AQL function on server.
 *
 * @package ArangoDB\AQL\Functions
 * @author  Lucas S. Vieira
 */
class AQLFunction implements EntityInterface
{
    /**
     * Fully qualified name of user function.
     */
    protected string $name;

    /**
     * String representation of function body.
     */
    protected string $code;

    /**
     * An optional boolean value to indicate whether the function results are fully deterministic. <br>
     * (function return value solely depends on the input value and return value is the same for repeated calls with same input)
     */
    protected bool $isDeterministic;

    /**
     * If the entity is not a representation of an existing user function on server,
     * this property is true.
     */
    protected bool $isNew;

    /**
     * Connection object.
     */
    protected Connection|null $connection;

    /**
     * Stores the deletion data for an AQLFunction object.
     */
    protected array $deletion = [];

    /**
     * AQLFunction constructor.
     *
     * @param string $name The AQL function name.
     * @param string $code The AQL function code.
     * @param Connection|null $connection Connection object to use.
     * @param bool $isDeterministic Indicates if the function results are deterministic.
     * @param bool $isNew Indicates if the function object is a new one or not.
     */
    public function __construct(string $name, string $code, Connection $connection = null, bool $isDeterministic = true, bool $isNew = true)
    {
        $this->name = $name;
        $this->code = $code;
        $this->isNew = $isNew;
        $this->connection = $connection;
        $this->isDeterministic = $isDeterministic;
    }

    /**
     * If the AQLFunction object is a new created AQLFunction (and not exists on server) <br>
     * or if it is a representation of an existing one.
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * Returns the AQL function name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the AQL function code.
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Indicates if the function results are deterministic. <br>
     *
     * @return bool
     */
    public function isDeterministic(): bool
    {
        return $this->isDeterministic;
    }

    /**
     * If the object has performed a delete operation, this method will return the deletion data.
     *
     * @return array
     */
    public function getDeletionData(): array
    {
        return $this->deletion;
    }

    /**
     * If this AQL function has a connection set or not.
     *
     * @return bool True if it has a connection object. False otherwise.
     */
    public function hasConnection(): bool
    {
        return !($this->connection === null);
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
     * Saves the AQL function on server.
     *
     * @return bool True if operation was successful, false otherwise.
     *
     * @throws ServerException|GuzzleException
     */
    public function save(): bool
    {
        try {
            if ($this->hasConnection()) {
                $this->connection->post(Api::AQL_USER_FUNCTION, $this->toArray());
                $this->isNew = false;
                return true;
            }

            return false;
        } catch (ClientException $exception) {
            // Unknown error.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            throw new ServerException($response['errorMessage'], $exception, $response['errorNum']);
        }
    }

    /**
     * Removes an AQL function from server, if possible
     *
     * @return bool True if operation was successful, false otherwise
     *
     * @throws ServerException|GuzzleException
     */
    public function delete(): bool
    {
        try {
            if ($this->hasConnection()) {
                $response = $this->connection->delete(Api::addUriParam(Api::AQL_USER_FUNCTION, $this->name));
                $data = json_decode((string)$response->getBody(), true);
                $this->deletion = $data;
                return true;
            }

            return false;
        } catch (ClientException $exception) {
            // Unknown error.
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            throw new ServerException($response['errorMessage'], $exception, $response['errorNum']);
        }
    }

    /**
     * Returns a array representation of AQL function object.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'isDeterministic' => $this->isDeterministic,
        ];
    }

    /**
     * Return a JSON representation of AQL function object.
     *
     * @return array|mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
