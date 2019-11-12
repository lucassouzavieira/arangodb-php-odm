<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Auth;

use ArangoDB\Validation\Validator;
use ArangoDB\Validation\Rules\Rules;

/**
 * Validates user data <br>
 * Used for avoid client errors when creating new users or granting access to databases.
 *
 * @package ArangoDB\Validation\Auth
 * @author Lucas S. Vieira
 */
class UserValidator extends Validator
{
    /**
     * Required keys in users arrays.
     *
     * @var array
     */
    protected $required = [
        'user', 'active'
    ];

    /**
     * Users array can have these keys.
     *
     * @var array
     */
    protected $canHave = [
        'password', 'extra'
    ];

    /**
     * Rules for user.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user' => Rules::string(),
            'password' => Rules::string(),
            'active' => Rules::boolean(),
            'extra' => Rules::arr()
        ];
    }
}
