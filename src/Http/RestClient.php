<?php

namespace ArangoDB\Http;

use GuzzleHttp\Client;
use ArangoDB\Validation\Rules\Rules;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Class RestClient
 *
 * @package ArangoDB\Http
 * @author Lucas S. Vieira
 */
class RestClient
{
    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * RestClient constructor.
     *
     * @param string $baseUri base URI
     * @param array $options
     * @throws InvalidParameterException If the base uri is invalid
     */
    public function __construct(string $baseUri, array $options = [])
    {
        $validator = Rules::uri();

        if (!$validator->isValid($baseUri)) {
            throw new InvalidParameterException('baseUri', $baseUri);
        }

        $this->httpClient = new Client(array_merge(['base_uri' => $baseUri], $options));
    }

    /**
     * Performs a GET request
     *
     * @param string $url Url to execute request
     * @param mixed $data Data to send
     * @param array $headers Headers
     *
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     * @todo remove Content-Length on get requests
     */
    public function get($url, $data = [], $headers = []): ResponseInterface
    {
        $response = $this->httpClient->request('GET', $url, [
            'headers' => $headers,
            'body' => json_encode($data)
        ]);

        return $response;
    }

    /**
     * Performs a POST request
     *
     * @param string $url Url to execute request
     * @param mixed $data Data to send
     * @param array $headers Headers
     *
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function post($url, $data = [], $headers = []): ResponseInterface
    {
        $response = $this->httpClient->request('POST', $url, [
            'headers' => $headers,
            'body' => json_encode($data)
        ]);

        return $response;
    }

    /**
     * Performs a PUT request
     *
     * @param string $url Url to execute request
     * @param mixed $data Data to send
     * @param array $headers Headers
     *
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function put($url, $data = [], $headers = []): ResponseInterface
    {
        $response = $this->httpClient->request('PUT', $url, [
            'headers' => $headers,
            'body' => json_encode($data)
        ]);

        return $response;
    }

    /**
     * Performs a PATCH request
     *
     * @param string $url Url to execute request
     * @param mixed $data Data to send
     * @param array $headers Headers
     *
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function patch($url, $data = [], $headers = []): ResponseInterface
    {
        $response = $this->httpClient->request('PATCH', $url, [
            'headers' => $headers,
            'body' => json_encode($data)
        ]);

        return $response;
    }

    /**
     * Performs a DELETE request
     *
     * @param string $url Url to execute request
     * @param mixed $data Data to send
     * @param array $headers Headers
     *
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function delete($url, $data = [], $headers = []): ResponseInterface
    {
        $response = $this->httpClient->request('DELETE', $url, [
            'headers' => $headers,
            'body' => json_encode($data)
        ]);

        return $response;
    }
}
