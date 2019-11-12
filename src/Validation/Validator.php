<?php
declare(strict_types=1);

namespace ArangoDB\Validation;

use ArangoDB\Validation\Exceptions\InvalidParameterException;
use ArangoDB\Validation\Exceptions\MissingParameterException;

/**
 * Validator base class
 * Implements a default validate method
 *
 * @package ArangoDB\Validation
 */
abstract class Validator implements ValidatorInterface
{
    /**
     * Data to validate
     *
     * @var array
     */
    protected $data = [];

    /**
     * Required keys
     *
     * @var array
     */
    protected $required = [];

    /**
     * Optional keys
     *
     * @var array
     */
    protected $canHave = [];

    /**
     * Validator constructor.
     *
     * @param array $data Attributes to validate.
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Validate user data
     *
     * @return True if validation is successful, throw an exception otherwise.
     *
     * @throws MissingParameterException|InvalidParameterException
     */
    public function validate(): bool
    {
        foreach ($this->rules() as $ruleKey => $validator) {
            // Check for required keys.
            if (!array_key_exists($ruleKey, $this->data) && in_array($ruleKey, $this->required)) {
                throw new MissingParameterException($ruleKey);
            }

            // Can have keys may be not present.
            if (!array_key_exists($ruleKey, $this->data)) {
                continue;
            }

            // Validate given keys.
            if (!$validator->isValid($this->data[$ruleKey])) {
                throw new InvalidParameterException($ruleKey, $this->data[$ruleKey]);
            }
        }

        return true;
    }
}
