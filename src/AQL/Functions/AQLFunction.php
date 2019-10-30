<?php
declare(strict_types=1);

namespace ArangoDB\AQL\Functions;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use ArangoDB\Entity\EntityInterface;
use ArangoDB\Exceptions\ServerException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Represents an user defined task on server
 *
 * @package ArangoDB\AQL\Functions
 * @author Lucas S. Vieira
 */
class AQLFunction implements EntityInterface
{
    /**
     * Fully qualified name of user function
     *
     * @var string
     */
    protected $name;

    /**
     * String representation of function body
     *
     * @var string
     */
    protected $code;

    /**
     * an optional boolean value to indicate whether the function
     * results are fully deterministic
     * (function return value solely depends on the input value and return value is the same for repeated calls with same input)
     *
     * @var bool
     */
    protected $isDeterministic;

    /**
     * If the entity is not an representation of a existing user function on server,
     * this property is true.
     *
     * @var bool
     */
    protected $isNew;

    /**
     * Connection object
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Stores the deletion data for an AQLFunction object
     *
     * @var array
     */
    protected $deletion = [];

    /**
     * AQLFunction constructor.
     *
     * @param string $name
     * @param string $code
     * @param Connection|null $connection
     * @param bool $isDeterministic
     * @param bool $isNew
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
     * @return bool
     * @see EntityInterface::isNew()
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return bool
     */
    public function isDeterministic(): bool
    {
        return $this->isDeterministic;
    }

    /**
     * If the object has performed a delete operation, this method will return the deletion data
     *
     * @return array
     */
    public function getDeletionData(): array
    {
        return $this->deletion;
    }

    /**
     * If this AQL function has a connection set or not
     *
     * @return bool True if has a connection object. False otherwise.
     */
    public function hasConnection(): bool
    {
        return !is_null($this->connection);
    }

    /**
     * Sets a connection to use
     *
     * @param Connection $connection
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return bool true if operation was successful, false otherwise
     * @throws ServerException|GuzzleException
     * @see EntityInterface::save()
     */
    public function save(): bool
    {
        try {
            if ($this->hasConnection()) {
                $response = $this->connection->post(Api::AQL_USER_FUNCTION, $this->toArray());
                $data = json_decode((string)$response->getBody(), true);
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
     * @return bool true if operation was successful, false otherwise
     * @throws ServerException|GuzzleException
     * @see EntityInterface::delete()
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
            $serverException = new ServerException($response['errorMessage'], $exception, $response['errorNum']);
            throw $serverException;
        }
    }

    /**
     * @return array
     * @see EntityInterface::toArray()
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
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
