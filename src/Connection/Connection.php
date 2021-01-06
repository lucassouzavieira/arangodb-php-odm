<?php
declare(strict_types=1);

namespace ArangoDB\Connection;

use ArangoDB\Database\Database;
use ArangoDB\Auth\Authenticable;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Auth\Exceptions\AuthException;
use ArangoDB\Exceptions\ConnectionException;
use ArangoDB\Exceptions\Database\DatabaseException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use ArangoDB\Validation\Connection\ConnectionOptionsValidator;

/**
 * Represents a connection with an ArangoDB database.
 *
 * @package ArangoDB\Connection
 * @author Lucas S. Vieira
 */
class Connection extends Authenticable
{
    /**
     * Default headers to send to server.
     *
     * @var array
     */
    protected $defaultHeaders = [];

    /**
     * Connection constructor.
     *
     * @param array $options Connection options.
     *
     * @throws InvalidParameterException|MissingParameterException|GuzzleException|AuthException|ConnectionException
     */
    public function __construct(array $options)
    {
        $validator = new ConnectionOptionsValidator($options);
        $validator->validate();
        parent::__construct($validator->getConnectionOptions());
    }

    /**
     * Proper debug dump for connection objects.
     *
     * @return array
     */
    public function __debugInfo()
    {
        $options = $this->options;
        unset($options['username'], $options['password']);

        return [
            'options' => $options,
            'defaultHeaders' => $this->getDefaultHeaders(),
            'authenticated' => $this->isAuthenticated()
        ];
    }

    /**
     * If connection is authenticated.
     *
     * @return bool True if connection already authenticate, false otherwise.
     */
    public function isAuthenticated(): bool
    {
        return is_array($this->authToken);
    }

    /**
     * Return the connection default headers.
     *
     * @return array
     */
    public function getDefaultHeaders(): array
    {
        return $this->defaultHeaders;
    }

    /**
     * Set the connection default headers.
     *
     * @param array $headers The default array to add on all requests.
     */
    public function setDefaultHeaders(array $headers)
    {
        $this->defaultHeaders = $headers;
    }


    /**
     * Return the base endpoint URI.
     *
     * @return string
     */
    public function getBaseUri(): string
    {
        return $this->options['endpoint'];
    }

    /**
     * Return the database object for this connection
     *
     * @return Database
     *
     * @throws GuzzleException|DatabaseException|InvalidParameterException|MissingParameterException
     */
    public function getDatabase(): Database
    {
        return new Database($this);
    }

    /**
     * Return the name of database handled
     *
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->options['database'];
    }

    /**
     * Returns the name of user on server
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->options['username'];
    }

    /**
     * Executes a GET request on server.
     *
     * @param string $endpoint URI to make the request.
     * @param array $body The body content.
     * @param array $headers Additional headers to send on request.
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function get(string $endpoint, array $body = [], array $headers = []): ResponseInterface
    {
        return $this->restClient->get($endpoint, $body, array_merge($headers, $this->getDefaultHeaders(), $this->getAuthorizationHeader()));
    }

    /**
     * Executes a POST request on server.
     *
     * @param string $endpoint URI to make the request.
     * @param array $body The body content.
     * @param array $headers Additional headers to send on request.
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function post(string $endpoint, array $body = [], array $headers = []): ResponseInterface
    {
        return $this->restClient->post($endpoint, $body, array_merge($headers, $this->getDefaultHeaders(), $this->getAuthorizationHeader()));
    }

    /**
     * Executes a PUT request on server.
     *
     * @param string $endpoint URI to make the request.
     * @param array $body The body content.
     * @param array $headers Additional headers to send on request.
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function put(string $endpoint, array $body = [], array $headers = []): ResponseInterface
    {
        return $this->restClient->put($endpoint, $body, array_merge($headers, $this->getDefaultHeaders(), $this->getAuthorizationHeader()));
    }

    /**
     * Executes a PATCH request on server.
     *
     * @param string $endpoint URI to make the request.
     * @param array $body The body content.
     * @param array $headers Additional headers to send on request.
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function patch(string $endpoint, array $body = [], array $headers = []): ResponseInterface
    {
        return $this->restClient->patch($endpoint, $body, array_merge($headers, $this->getDefaultHeaders(), $this->getAuthorizationHeader()));
    }

    /**
     * Executes a DELETE request on server.
     *
     * @param string $endpoint URI to make the request.
     * @param array $body The body content.
     * @param array $headers Additional headers to send on request.
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function delete(string $endpoint, array $body = [], array $headers = []): ResponseInterface
    {
        return $this->restClient->delete($endpoint, $body, array_merge($headers, $this->getDefaultHeaders(), $this->getAuthorizationHeader()));
    }

    /**
     * Makes a custom HTTP request.
     *
     * @param string $method HTTP Method to use.
     * @param string $uri URI to make the request.
     * @param string $body The body content.
     * @param array $headers Additional headers to send on request.
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function customHttpRequest(string $method, string $uri, string $body = "", array $headers = []): ResponseInterface
    {
        return $this->restClient->customHttpRequest($method, $uri, $body, array_merge($headers, $this->getDefaultHeaders(), $this->getAuthorizationHeader()));
    }
}
