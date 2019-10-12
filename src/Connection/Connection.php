<?php
declare(strict_types=1);

namespace ArangoDB\Connection;

use ArangoDB\Auth\Authenticable;
use GuzzleHttp\Exception\GuzzleException;
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
     * @throws InvalidParameterException|MissingParameterException|GuzzleException
     */
    public function __construct(array $options)
    {
        $validator = new ConnectionOptionsValidator($options);
        $validator->validate();
        parent::__construct($options);
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
}
