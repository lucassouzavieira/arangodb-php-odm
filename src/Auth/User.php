<?php
declare(strict_types=1);

namespace ArangoDB\Auth;

use ArangoDB\Http\Api;
use ArangoDB\Entity\Entity;
use ArangoDB\Validation\Rules\Rules;
use ArangoDB\DataStructures\ArrayList;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Auth\Exceptions\DuplicateUserException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Represents a user in server
 *
 * @package ArangoDB\Auth
 */
class User extends Entity
{
    /**
     * Name of user on server
     * @var string
     */
    protected $user;

    /**
     * Password of user.
     * Used only of new users that will be created on server.
     *
     * @var string
     */
    protected $password;

    /**
     * If user is active or not on database
     *
     * @var bool
     */
    protected $active;

    /**
     * Extra data about the user
     *
     * @var array|null
     */
    protected $extra;

    /**
     * User constructor.
     *
     * @param array $attributes
     * @param bool $isNew
     * @throws \ReflectionException
     */
    public function __construct(array $attributes = [], bool $isNew = true)
    {
        $this->initialize(['user', 'password', 'active', 'extra'], $attributes);
        parent::__construct($attributes, $isNew);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->attributes['active'];
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->attributes['active'] = $active;
    }

    /**
     * Returns the username
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->attributes['user'];
    }

    /**
     * @return array|null
     */
    public function getExtra()
    {
        return $this->attributes['extra'];
    }

    /**
     * @param array $extra
     */
    public function setExtra(array $extra): void
    {
        $this->attributes['extra'] = $extra;
    }

    /**
     * @return array
     * @see Entity::toArray()
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
     * Finds a user on server
     *
     * @param string $username
     * @return User|null User if exists, null if not
     * @throws GuzzleException|InvalidParameterException|\ReflectionException
     */
    public function find(string $username)
    {
        try {
            $response = $this->connection->get($this->getEntityBaseUri($username));
            $data = json_decode((string)$response->getBody(), true);
            $user = new User($data, false);
            $user->setConnection($this->connection);
            return $user;
        } catch (ClientException $exception) {
            // User not found.
            if ($exception->getResponse()->getStatusCode() == 404) {
                return null;
            }
        }
    }

    /**
     * Return all users from server
     *
     * @return ArrayList[User] An ArrayList of users
     * @throws GuzzleException|\ReflectionException|InvalidParameterException
     * @see Entity::all()
     */
    public function all(): ArrayList
    {
        $response = $this->connection->get($this->getEntityBaseUri());
        $data = json_decode((string)$response->getBody(), true);
        return self::make($data['result']);
    }

    /**
     * Saves (or update) a user on server
     *
     * @return bool True if user was created or updated on server. Throws an exception if user is duplicated
     * @throws DuplicateUserException
     * @throws InvalidParameterException
     */
    public function save(): bool
    {
        try {
            $method = $this->isNew() ? 'post' : 'patch';
            $uri = $this->isNew() ? $this->getEntityBaseUri() : $this->getEntityBaseUri($this->getUsername());

            $this->connection->$method($uri, $this->attributes);
            $this->isNew = false;
            $this->password = null;

            return true;
        } catch (ClientException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            $duplicatedException = new DuplicateUserException($response['errorMessage'], $exception, $response['errorNum']);
            throw $duplicatedException;
        }
    }

    /**
     * Removes an user from server
     *
     * @return bool True if user was removed, false otherwise (e.g. user not exists)
     * @throws GuzzleException|InvalidParameterException
     * @see Entity::delete()
     */
    public function delete(): bool
    {
        try {
            $uri = $this->getEntityBaseUri($this->getUsername());
            $response = $this->connection->delete($uri, $this->toArray());

            $this->isNew = false;
            $this->password = null;

            return true;
        } catch (ClientException $exception) {
            // User not found.
            if ($exception->getResponse()->getStatusCode() == 404) {
                return false;
            }
        }
    }

    /**
     * Returns base uri for handle entity
     *
     * @param string|int $parameter
     * @return string URI for handle entity
     * @throws InvalidParameterException
     */
    protected function getEntityBaseUri($parameter = null): string
    {
        $integerValidator = Rules::integer();
        $stringValidator = Rules::string();

        $uri = Api::buildUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::USER);
        if (is_null($parameter)) {
            return $uri;
        }

        if ($integerValidator->isValid($parameter) || $stringValidator->isValid($parameter)) {
            return sprintf("%s/%s", $uri, $parameter);
        }

        throw new InvalidParameterException('parameter', $parameter);
    }

    /**
     * Make a series of User objects
     *
     * @param array $data
     * @param bool $isNew
     * @return ArrayList[User]
     * @throws \ReflectionException
     * @see Entity::save()
     */
    protected function make(array $data = [], bool $isNew = false): ArrayList
    {
        $list = new ArrayList();
        foreach ($data as $userData) {
            $user = new User($data, $isNew);
            $user->setConnection($this->connection);
            $list->put($userData['user'], $user);
        }

        return $list;
    }
}
