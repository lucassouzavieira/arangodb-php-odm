<?php
declare(strict_types=1);

namespace ArangoDB\Validation;

use ArangoDB\Validation\Rules\Rules;
use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Class ConnectionOptionsValidator
 *
 * @package ArangoDB\Connection
 * @copyright 2019 Lucas S. Vieira
 */
class ConnectionOptionsValidator implements ValidatorInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * Mandatory keys in connections arrays
     * @var array
     */
    protected $required = [
        'endpoint', 'database', 'username', 'password'
    ];

    /**
     * The connection array can have these keys
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
     * Validate the Connection options
     *
     * @return true if validation is successful, throw an exception otherwise
     * @throws MissingParameterException|InvalidParameterException
     */
    public function validate(): bool
    {
        foreach ($this->rules() as $ruleKey => $validator) {
            // Check for 'must have' keys.
            if (!array_key_exists($ruleKey, $this->options) && in_array($ruleKey, $this->required)) {
                throw new MissingParameterException($ruleKey);
            }

            // Can have keys may be not present.
            if (!array_key_exists($ruleKey, $this->options)) {
                continue;
            }

            // Validate given keys.
            if (!$validator->isValid($this->options[$ruleKey])) {
                throw new InvalidParameterException($ruleKey, $this->options[$ruleKey]);
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getConnectionOptions(): array
    {
        return $this->options;
    }
}
