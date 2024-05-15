<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Connection;

use ArangoDB\Validation\Validator;
use ArangoDB\Validation\Rules\Rules;

/**
 * Validate the connection options to access the database.
 *
 * @package ArangoDB\Validation\Connection
 * @author Lucas S. Vieira
 */
class ConnectionOptionsValidator extends Validator
{
    /**
     * Required keys in connections arrays.
     *
     * @var array
     */
    protected $required = [
        'endpoint', 'database', 'username', 'password'
    ];

    /**
     * The connection array can have these keys.
     *
     * @var array
     */
    protected $canHave = [
        'connection', 'timeout', 'reconnect', 'create', 'policy', 'host', 'port'
    ];

    /**
     * ConnectionOptionsValidator constructor.
     *
     * @param array $data Attributes to validate.
     */
    public function __construct(array $data = [])
    {
        // We can use 'host' and 'port' keys to define the endpoint.
        if (array_key_exists('host', $data) && array_key_exists('port', $data)) {
            $data['endpoint'] = sprintf("%s:%d", $data['host'], $data['port']);
        }

        parent::__construct($data);
    }

    /**
     * Rules for connection
     * @return array
     */
    public function rules(): array
    {
        return [
            'endpoint' => Rules::uri(),
            'database' => Rules::string(),
            'username' => Rules::string(),
            'password' => Rules::string(),
            'connection' => Rules::in(['Close', 'Keep-Alive']),
            'port' => Rules::numeric(),
            'timeout' => Rules::integer(),
            'policy' => Rules::in(['error', 'last'])
        ];
    }

    /**
     * Return all connection options.
     *
     * @return array
     */
    public function getConnectionOptions(): array
    {
        return $this->data;
    }
}
