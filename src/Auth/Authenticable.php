<?php
declare(strict_types=1);

namespace ArangoDB\Auth;

use ArangoDB\Http\Api;
use ArangoDB\Http\RestClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use ArangoDB\Auth\Exceptions\AuthException;
use ArangoDB\Exceptions\ConnectionException;
use GuzzleHttp\Exception\BadResponseException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Class Authenticable
 *
 * @package ArangoDB\Auth
 * @author Lucas S. Vieira
 */
abstract class Authenticable
{
    /**
     * @var array
     */
    protected array $options;

    /**
     * @var array JWT token
     */
    protected array $authToken;

    /**
     * @var RestClient
     */
    protected RestClient $restClient;

    /**
     * Authenticable constructor.
     *
     * @param array $options
     * @throws InvalidParameterException|GuzzleException|AuthException|ConnectionException
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
     * @throws RequestException|AuthException|GuzzleException|ConnectionException
     */
    protected function authenticate(array $credentials): void
    {
        try {
            $response = $this->restClient->post($this->getAuthenticationEndpoint(), $credentials);
            $this->authToken = json_decode((string)$response->getBody(), true);
        } catch (BadResponseException $exception) {
            $response = json_decode((string)$exception->getResponse()->getBody(), true);
            throw new AuthException($response['errorMessage'], $exception, $response['errorNum']);
        } catch (ConnectException $exception) {
            throw new ConnectionException($exception->getMessage(), $exception);
        }
    }

    /**
     * Return the authorization header
     *
     * @return array
     */
    protected function getAuthorizationHeader()
    {
        $header = [];
        if (is_array($this->authToken)) {
            $header = [
                'Authorization' => sprintf("Bearer %s", $this->authToken['jwt'])
            ];
        }

        return $header;
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
     * @return string
     */
    private function getAuthenticationEndpoint()
    {
        return sprintf(Api::DB . "%s" . Api::AUTH_BASE, $this->getDatabaseName());
    }
}
