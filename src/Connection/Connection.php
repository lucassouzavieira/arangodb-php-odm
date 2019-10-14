<?php
declare(strict_types=1);

namespace ArangoDB\Connection;

use ArangoDB\Auth\Authenticable;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Auth\Exceptions\AuthException;
use ArangoDB\Exceptions\ConnectionException;
use ArangoDB\Validation\ConnectionOptionsValidator;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Class Connection
 *
 * @package ArangoDB\Connection
 * @copyright 2019 Lucas S. Vieira
 */
class Connection extends Authenticable
{
    /**
     * Connection constructor.
     *
     * @param array $options Connection options
     * @throws InvalidParameterException|MissingParameterException|GuzzleException|AuthException|ConnectionException
     */
    public function __construct(array $options)
    {
        $validator = new ConnectionOptionsValidator($options);
        $validator->validate();
        parent::__construct($validator->getConnectionOptions());
    }

    /**
     * If connection is authenticated
     *
     * @return bool True if connection already authenticate, false otherwise
     */
    public function isAuthenticated(): bool
    {
        return is_array($this->authToken);
    }

    /**
     * Return the base endpoint uri
     * @return string
     */
    public function getBaseUri(): string
    {
        return $this->options['endpoint'];
    }

    /**
     * Return the name of database handled
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->options['database'];
    }

    /**
     * Executes a GET request on server
     *
     * @param string $endpoint
     * @param array $body
     * @param array $headers
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get(string $endpoint, array $body = [], array $headers = []): ResponseInterface
    {
        return $this->restClient->get($endpoint, $body, array_merge($headers, $this->getAuthorizationHeader()));
    }

    /**
     * Executes a POST request on server
     *
     * @param string $endpoint
     * @param array $body
     * @param array $headers
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function post(string $endpoint, array $body = [], array $headers = []): ResponseInterface
    {
        return $this->restClient->post($endpoint, $body, array_merge($headers, $this->getAuthorizationHeader()));
    }

    /**
     * Executes a PUT request on server
     *
     * @param string $endpoint
     * @param array $body
     * @param array $headers
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function put(string $endpoint, array $body = [], array $headers = []): ResponseInterface
    {
        return $this->restClient->put($endpoint, $body, array_merge($headers, $this->getAuthorizationHeader()));
    }

    /**
     * Executes a PATCH request on server
     *
     * @param string $endpoint
     * @param array $body
     * @param array $headers
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function patch(string $endpoint, array $body = [], array $headers = []): ResponseInterface
    {
        return $this->restClient->patch($endpoint, $body, array_merge($headers, $this->getAuthorizationHeader()));
    }

    /**
     * Executes a DELETE request on server
     *
     * @param string $endpoint
     * @param array $body
     * @param array $headers
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function delete(string $endpoint, array $body = [], array $headers = []): ResponseInterface
    {
        return $this->restClient->delete($endpoint, $body, array_merge($headers, $this->getAuthorizationHeader()));
    }
}
