<?php
declare(strict_types=1);

namespace ArangoDB\Auth;

use ArangoDB\Http\Api;
use ArangoDB\Http\RestClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Class Authenticable
 *
 * @package ArangoDB\Auth
 */
abstract class Authenticable
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var array JWT token
     */
    protected $authToken;

    /**
     * @var RestClient
     */
    protected $restClient;

    /**
     * Authenticable constructor.
     *
     * @param array $options
     * @throws InvalidParameterException|GuzzleException
     */
    public function __construct(array $options)
    {
        $this->options = $options;
        $this->restClient = new RestClient($this->options['endpoint']);
        $this->authenticate($this->getCredentials());
    }

    /**
     * Authenticates a user on ArangoDB Server
     *
     * @param array $credentials
     * @throws RequestException|GuzzleException|ClientException
     */
    protected function authenticate(array $credentials): void
    {
        $response = $this->restClient->post($this->getAuthenticationEndpoint($this->options['database']), $credentials);
        $this->authToken = json_decode((string)$response->getBody(), true);
    }

    /**
     * Return the authorization header
     *
     * @return array
     */
    protected function getAuthorizationHeader()
    {
        if (is_array($this->authToken)) {
            return [
                'Authorization' => sprintf("Bearer %s", $this->authToken['jwt'])
            ];
        }

        return [];
    }

    /**
     * Return authentication credentials
     * @return array
     */
    private function getCredentials(): array
    {
        return [
            'username' => $this->options['username'],
            'password' => $this->options['password'],
        ];
    }

    /**
     * Authentication endpoint for a given database
     *
     * @param string $database
     * @return string
     */
    private function getAuthenticationEndpoint(string $database)
    {
        return sprintf(Api::DB . "%s" . Api::AUTH_BASE, $this->getDatabaseName());
    }
}
