<?php
declare(strict_types=1);

namespace ArangoDB\Auth;

use ArangoDB\Http\Api;
use ArangoDB\Connection\Connection;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Auth\Exceptions\UserException;
use ArangoDB\Validation\Auth\UserValidator;
use ArangoDB\Entity\Contracts\EntityInterface;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Represents a user in server.
 *
 * @package ArangoDB\Auth
 * @author Lucas S. Vieira
 */
class User implements EntityInterface
{
    /**
     * Name of user on server.
     *
     * @var string
     */
    protected $user;

    /**
     * Password of user.
     * Used only of new users that will be created on server.
     *
     * @var string
     */
    protected $password = '';

    /**
     * If user is active or not on database.
     *
     * @var bool
     */
    protected $active;

    /**
     * Extra data about the user.
     *
     * @var array|null
     */
    protected $extra = null;

    /**
     * If user is a new User or an existing one.
     *
     * @var bool
     */
    protected $isNew;

    /**
     * Connection object to use.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * User constructor.
     *
     * @param array $attributes User attributes.
     * @param Connection|null $connection Connection object to use.
     * @param bool $isNew If the user is a new one or representation of an existing user.
     *
     * @throws InvalidParameterException|MissingParameterException
     */
    public function __construct(array $attributes = [], Connection $connection = null, bool $isNew = true)
    {
        $validator = new UserValidator($attributes);
        $validator->validate();

        $this->isNew = $isNew;
        $this->connection = $connection;
        $this->user = $attributes['user'];
        $this->active = $attributes['active'];

        if (isset($attributes['password'])) {
            $this->password = $attributes['password'];
        }

        if (isset($attributes['extra'])) {
            $this->extra = $attributes['extra'];
        }
    }

    /**
     * Proper debug dump for User objects.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->toArray();
    }

    /**
     * String representation of User object.
     *
     * @return false|mixed|string
     */
    public function __toString()
    {
        return print_r($this->toArray(), true);
    }

    /**
     * Get some attribute.
     *
     * @param string $name Attribute name.
     *
     * @return mixed|null Attribute value.
     */
    public function __get($name)
    {
        if (in_array($name, ['extra', 'user', 'active'])) {
            return $this->{$name};
        }

        return null;
    }

    /**
     * Get the activation status of the user.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Returns true if is a new user.
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * Set the activation status of the user.
     *
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * Returns the username.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->user;
    }

    /**
     * Returns extra data about user.
     *
     * @return array|null
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * Set extra data for user.
     *
     * @param array $extra
     */
    public function setExtra(array $extra): void
    {
        $this->extra = $extra;
    }

    /**
     * Returns a array representation of user
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'user' => $this->user,
            'active' => $this->active,
            'extra' => $this->extra
        ];
    }

    /**
     * Saves (or update) a user on server
     *
     * @return bool True if user was created or updated on server. Throws an exception if user is duplicated.
     *
     * @throws UserException
     */
    public function save(): bool
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::USER);

            $method = $this->isNew() ? 'post' : 'patch'; // User will be created or updated.
            $uri = $this->isNew() ? $uri : Api::addUriParam($uri, $this->getUsername());
            $data = $this->toArray();
            $data['password'] = $this->password;

            $this->connection->$method($uri, $data);
            $this->isNew = false;
            $this->password = null;

            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $duplicatedException = new UserException($response['errorMessage'], $exception, $response['errorNum']);
            throw $duplicatedException;
        }
    }

    /**
     * Removes an user from server.
     *
     * @return bool True if user was removed, false otherwise (e.g. user not exists).
     *
     * @throws GuzzleException|InvalidParameterException|UserException
     */
    public function delete(): bool
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::USER);
            $uri = Api::addUriParam($uri, $this->getUsername());

            $response = $this->connection->delete($uri);
            $this->isNew = false;
            $this->password = null;

            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $userException = new UserException($response['errorMessage'], $exception, $response['errorNum']);

            // User not found.
            if ($exception->getResponse()->getStatusCode() == 404) {
                return false;
            }

            throw $userException;
        }
    }

    /**
     * Return a JSON representation of list.
     *
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
