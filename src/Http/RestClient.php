<?php

namespace ArangoDB\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use ArangoDB\Validation\Rules\Rules;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\GuzzleException;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * RestClient.
 *
 * @package ArangoDB\Http
 * @author Lucas S. Vieira
 */
class RestClient
{
    /**
     * Base URI string.
     *
     * @var string
     */
    protected $baseUri;

    /**
     * Guzzle HTTP client.
     *
     * @var Client
     */
    protected $httpClient;

    /**
     * RestClient constructor.
     *
     * @param string $baseUri Base URI for all requests.
     * @param array $options Base options for all requests.
     *
     * @throws InvalidParameterException If the base URI is invalid.
     */
    public function __construct(string $baseUri, array $options = [])
    {
        $validator = Rules::uri();

        if (!$validator->isValid($baseUri)) {
            throw new InvalidParameterException('baseUri', $baseUri);
        }

        $this->baseUri = $baseUri;
        $this->httpClient = new Client(array_merge(['base_uri' => $baseUri, 'headers' => null], $options));
    }

    /**
     * Performs a GET request.
     *
     * @param string $url URL to execute request.
     * @param mixed $data Data to send.
     * @param array $headers Additional headers.
     *
     * @return mixed|ResponseInterface
     *
     * @throws GuzzleException
     */
    public function get($url, $data = [], $headers = []): ResponseInterface
    {
        $request = new Request('GET', $url, $headers, json_encode($data));
        return $this->httpClient->send($request->withoutHeader('content-length'));
    }

    /**
     * Performs a POST request.
     *
     * @param string $url URL to execute request.
     * @param mixed $data Data to send.
     * @param array $headers Additional headers.
     *
     * @return mixed|ResponseInterface
     *
     * @throws GuzzleException
     */
    public function post($url, $data = [], $headers = []): ResponseInterface
    {
        $request = new Request('POST', $url, $headers, json_encode($data));
        return $this->httpClient->send($request);
    }

    /**
     * Performs a PUT request.
     *
     * @param string $url URL to execute request.
     * @param mixed $data Data to send.
     * @param array $headers Additional headers.
     *
     * @return mixed|ResponseInterface
     *
     * @throws GuzzleException
     */
    public function put($url, $data = [], $headers = []): ResponseInterface
    {
        $request = new Request('PUT', $url, $headers, json_encode($data));
        return $this->httpClient->send($request);
    }

    /**
     * Performs a PATCH request
     *
     * @param string $url URL to execute request
     * @param mixed $data Data to send
     * @param array $headers Additional headers.
     *
     * @return mixed|ResponseInterface
     *
     * @throws GuzzleException
     */
    public function patch($url, $data = [], $headers = []): ResponseInterface
    {
        $request = new Request('PATCH', $url, $headers, json_encode($data));
        return $this->httpClient->send($request);
    }

    /**
     * Performs a DELETE request.
     *
     * @param string $url URL to execute request.
     * @param mixed $data Data to send.
     * @param array $headers Additional headers.
     *
     * @return mixed|ResponseInterface
     *
     * @throws GuzzleException
     */
    public function delete($url, $data = [], $headers = []): ResponseInterface
    {
        $request = new Request('DELETE', $url, $headers, json_encode($data));
        return $this->httpClient->send($request);
    }

    /**
     * Makes a custom HTTP request.
     *
     * @param string $method HTTP method to use.
     * @param string $url URL to request.
     * @param string $body Body to sent.
     * @param array $headers Additional headers.
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function customHttpRequest(string $method, string $url, string $body = "", array $headers = []): ResponseInterface
    {
        $request = new Request($method, $url, $headers, $body);
        return $this->httpClient->send($request);
    }
}
