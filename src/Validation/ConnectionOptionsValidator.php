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
        'endpoint', 'database', 'user', 'pwd'
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
            'user' => Rules::string(),
            'pwd' => Rules::string(),
            'connection' => Rules::in(['Close', 'Keep-Alive']),
            'port' => Rules::integer(),
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
}
