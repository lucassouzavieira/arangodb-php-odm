<?php
declare(strict_types=1);

namespace ArangoDB\Auth;

use ArangoDB\Http\Api;
use ArangoDB\Entity\Entity;
use ArangoDB\DataStructures\ArrayList;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Validation\Exceptions\DuplicateUserException;

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
     * @var array
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
     * Returns the username
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->attributes['user'];
    }


    /**
     * @return ArrayList[User]
     * @throws GuzzleException|\ReflectionException
     * @see Entity::all()
     */
    public function all(): ArrayList
    {
        $uri = Api::buildUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::USER);
        $response = $this->connection->get($uri);
        $data = json_decode((string)$response->getBody(), true);
        return User::make($data['result']);
    }

    /**
     * @return bool
     * @throws DuplicateUserException
     * @throws GuzzleException
     * @see Entity::save()
     */
    public function save(): bool
    {
        try {
            $uri = Api::buildUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::USER);
            $response = $this->connection->post($uri, $this->toArray());
            $data = json_decode((string)$response->getBody(), true);

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
     * @return bool
     * @throws GuzzleException
     * @see Entity::delete()
     */
    public function delete(): bool
    {
        try {
            $uri = Api::buildUri($this->connection->getBaseUri(), $this->connection->getDatabaseName(), Api::USER);
            $response = $this->connection->delete(sprintf("%s/%s", $uri, $this->user), $this->toArray());
            $data = json_decode((string)$response->getBody(), true);

            $this->isNew = false;
            $this->password = null;

            return true;
        } catch (ClientException $exception) {
            // User not found.
            if ($exception->getResponse()->getStatusCode() == 404) {
                return false;
            }

            throw $exception;
        }
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
     * @param array $data
     * @param bool $isNew
     * @return ArrayList[User]
     * @throws \ReflectionException
     * @see Entity::save()
     */
    public static function make(array $data = [], bool $isNew = false): ArrayList
    {
        $list = new ArrayList();
        foreach ($data as $userData) {
            $list->put($userData['user'], new User($data, $isNew));
        }

        return $list;
    }
}
