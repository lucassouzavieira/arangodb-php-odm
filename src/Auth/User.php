<?php
declare(strict_types=1);

namespace ArangoDB\Auth;

use ArangoDB\Entity\EntityInterface;
use ArangoDB\Http\Api;
use ArangoDB\Entity\Entity;
use ArangoDB\Validation\Rules\Rules;
use ArangoDB\DataStructures\ArrayList;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Validation\Auth\UserValidator;
use ArangoDB\Auth\Exceptions\UserException;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Represents a user in server
 *
 * @package ArangoDB\Auth
 * @author Lucas S. Vieira
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
     * If user is a new User or an existing one
     *
     * @var bool
     */
    protected $isNew;

    /**
     * User constructor.
     *
     * @param array $attributes
     * @param bool $isNew
     * @throws \ReflectionException
     */
    public function __construct(array $attributes = [], bool $isNew = true)
    {
        $this->setAttributes(['user', 'password', 'active', 'extra'], $attributes);
        parent::__construct($attributes, $isNew);
    }

    /**
     * String representation of User object
     *
     * @return false|mixed|string
     */
    public function __toString()
    {
        return print_r($this->toArray(), true);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->attributes['active'];
    }

    /**
     * Returns true if is a new user
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew;
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
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::USER);
            $uri = Api::addUriParam($uri, $username);

            $response = $this->connection->get($uri);
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
     * Saves (or update) a user on server
     *
     * @return bool True if user was created or updated on server. Throws an exception if user is duplicated
     * @throws UserException
     * @see EntityInterface::save()
     */
    public function save(): bool
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::USER);

            $method = $this->isNew() ? 'post' : 'patch';
            $uri = $this->isNew() ? $uri : Api::addUriParam($uri, $this->getUsername());

            $this->connection->$method($uri, $this->attributes);
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
     * Removes an user from server
     *
     * @return bool True if user was removed, false otherwise (e.g. user not exists)
     * @throws GuzzleException|InvalidParameterException
     * @see EntityInterface::delete()
     */
    public function delete(): bool
    {
        try {
            $uri = Api::buildDatabaseUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::USER);
            $uri = Api::addUriParam($uri, $this->getUsername());

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
     * Initialize a handler object with given attributes
     *
     * @param array $attributesNames
     * @param array $attributes
     * @throws \ReflectionException
     */
    protected function setAttributes(array $attributesNames, array $attributes = [])
    {
        foreach ($attributesNames as $attribute) {
            $reflection = new \ReflectionClass($this);

            if (array_key_exists($attribute, $attributes)) {
                $reflectionProperty = $reflection->getProperty($attribute);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($this, $attributes[$attribute]);
                $reflectionProperty->setAccessible(false);
            }
        }
    }
}
