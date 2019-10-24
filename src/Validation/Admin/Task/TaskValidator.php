<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Admin\Task;

use ArangoDB\Validation\Validator;
use ArangoDB\Validation\Rules\Rules;
use ArangoDB\Validation\Exceptions\InvalidParameterException;

/**
 * Validates Task data
 *
 * @package ArangoDB\Validation\Admin\Task
 */
class TaskValidator extends Validator
{
    /**
     * Required keys
     *
     * @var array
     */
    protected $required = [
        'params', 'offset', 'command', 'name', 'period'
    ];

    /**
     * Optional keys
     *
     * @var array
     */
    protected $canHave = [
        'id', 'type'
    ];

    /**
     * Rules for validation
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => Rules::string(),
            'params' => Rules::callbackValidation($this->validateParamsCallback()),
            'offset' => Rules::equalsOrGreaterThan(1),
            'command' => Rules::string(),
            'name' => Rules::string(),
            'period' => Rules::equalsOrGreaterThan(1),
            'type' => Rules::in(['periodic', 'timed']),
            'database' => Rules::string(),
            'created' => Rules::string()
        ];
    }

    /**
     * Validate 'params' option
     *
     * @return \Closure
     */
    protected function validateParamsCallback()
    {
        return function (array $params) {
            $validator = Rules::isPrimitive();
            foreach ($params as $variable => $value) {
                if (!$validator->isValid($value)) {
                    throw new InvalidParameterException("params[$variable]", $value);
                }
            }

            return true;
        };
    }
}
