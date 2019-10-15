<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Auth;

use ArangoDB\Validation\Validator;
use ArangoDB\Validation\Rules\Rules;

/**
 * Validates user data
 * Used for avoid client errors when creating new users or granting access to databases
 *
 * @package ArangoDB\Connection
 * @author Lucas S. Vieira
 */
class UserValidator extends Validator
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * Mandatory keys in users arrays
     *
     * @var array
     */
    protected $required = [
        'username', 'password', 'active'
    ];

    /**
     * Users array can have these keys
     *
     * @var array
     */
    protected $canHave = [
        'extra'
    ];

    /**
     * UserValidator constructor.
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
            'username' => Rules::string(),
            'passwd' => Rules::string(),
            'active' => Rules::boolean(),
            'extra' => Rules::arr()
        ];
    }
}
