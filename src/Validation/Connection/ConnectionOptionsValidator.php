<?php
declare(strict_types=1);

namespace ArangoDB\Validation;

use ArangoDB\Validation\Rules\Rules;

/**
 * Class ConnectionOptionsValidator
 *
 * @package ArangoDB\Connection
 * @author Lucas S. Vieira
 */
class ConnectionOptionsValidator extends Validator
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * Required keys in connections arrays
     *
     * @var array
     */
    protected $required = [
        'endpoint', 'database', 'username', 'password'
    ];

    /**
     * The connection array can have these keys
     *
     * @var array
     */
    protected $canHave = [
        'connection', 'timeout', 'reconnect', 'create', 'policy', 'host', 'port'
    ];

    /**
     * ConnectionOptionsValidator constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;

        // We can use 'host' and 'port' keys to define the endpoint.
        if (array_key_exists('host', $options) && array_key_exists('port', $options)) {
            $this->options['endpoint'] = sprintf("%s:%d", $options['host'], $options['port']);
        }
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
     * @return array
     */
    public function getConnectionOptions(): array
    {
        return $this->options;
    }
}
