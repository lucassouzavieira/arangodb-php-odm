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
 * @copyright 2019 Lucas S. Vieira
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
     * @throws InvalidParameterException
     */
    public function __construct(string $baseUri)
    {
        $validator = Rules::uri();

        if (!$validator->isValid($baseUri)) {
            throw new InvalidParameterException('baseUri', $baseUri);
        }

        $this->httpClient = new Client(['base_uri' => $baseUri]);
    }

    /**
     * @param string $url Url to execute request
     * @param mixed $data Data to send
     * @param array $headers Headers
     *
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function get($url, $data = [], $headers = [])
    {
        $response = $this->httpClient->request('GET', $url, [
            'headers' => $headers,
            'body' => json_encode($data)
        ]);

        return $response;
    }

    /**
     * @param string $url Url to execute request
     * @param mixed $data Data to send
     * @param array $headers Headers
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function post($url, $data = [], $headers = [])
    {
        $response = $this->httpClient->request('POST', $url, [
            'headers' => $headers,
            'body' => json_encode($data)
        ]);

        return $response;
    }
}
