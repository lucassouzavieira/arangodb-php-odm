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
     * Validate user data
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
