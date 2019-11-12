<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Rules;

/**
 * Provides validation rules for inputs data.
 *
 * @package ArangoDB\Validation
 * @author Lucas S. Vieira
 */
abstract class Rules
{
    /**
     * Is array rule.
     *
     * @return RuleInterface
     */
    public static function arr()
    {
        return new class implements RuleInterface {
            /**
             * Check if a given value is an array.
             *
             * @param mixed $value Value to validate.
             *
             * @return bool True if value is valid, false otherwise.
             */
            public function isValid($value): bool
            {
                return is_array($value);
            }
        };
    }

    /**
     * Is string rule.
     *
     * @return RuleInterface
     */
    public static function string()
    {
        return new class implements RuleInterface {
            /**
             * Check if a given value is string.
             *
             * @param mixed $value Value to validate.
             *
             * @return bool True if value is valid, false otherwise.
             */
            public function isValid($value): bool
            {
                return is_string($value);
            }
        };
    }

    /**
     * Is numeric rule.
     *
     * @return RuleInterface
     */
    public static function numeric()
    {
        return new class implements RuleInterface {
            /**
             * Check if a given value is numeric.
             *
             * @param mixed $value Value to validate.
             *
             * @return bool True if value is valid, false otherwise.
             */
            public function isValid($value): bool
            {
                return is_numeric($value);
            }
        };
    }

    /**
     * Is integer rule.
     *
     * @return RuleInterface
     */
    public static function integer()
    {
        return new class implements RuleInterface {
            /**
             * Check if a given value is an integer.
             *
             * @param mixed $value Value to validate.
             *
             * @return bool True if value is valid, false otherwise.
             */
            public function isValid($value): bool
            {
                return is_integer($value);
            }
        };
    }

    /**
     * Is boolean rule.
     *
     * @return RuleInterface
     */
    public static function boolean()
    {
        return new class implements RuleInterface {
            /**
             * Check if a given value is a boolean.
             *
             * @param mixed $value Value to validate.
             *
             * @return bool True if value is valid, false otherwise.
             */
            public function isValid($value): bool
            {
                return is_bool($value);
            }
        };
    }

    /**
     * Is a primitive type rule.
     *
     * @return RuleInterface
     * @see https://www.php.net/manual/en/language.types.intro.php
     */
    public static function isPrimitive()
    {
        return new class implements RuleInterface {
            /**
             * Check if a given value is a primitive type
             *
             * @param mixed $value Value to validate.
             *
             * @return bool True if value is valid, false otherwise.
             */
            public function isValid($value): bool
            {
                if (is_object($value) || is_callable($value)) {
                    return false;
                }

                return is_null($value) || is_int($value) || is_string($value) || is_bool($value) || is_float($value);
            }
        };
    }

    /**
     * If a value is equals to or greater than some given value.
     *
     * @param int $reference Reference value.
     *
     * @return RuleInterface
     */
    public static function equalsOrGreaterThan(int $reference)
    {
        return new class($reference) implements RuleInterface {
            /**
             * Reference value.
             *
             * @var integer
             */
            protected $reference = 0;

            /**
             * equalsOrGreaterThan Rule constructor.
             *
             * @param int $reference Reference value.
             */
            public function __construct(int $reference)
            {
                $this->reference = $reference;
            }

            /**
             * Check if a given value is valid
             *
             * @param mixed $value Value to validate.
             *
             * @return bool True if value is valid, false otherwise.
             */
            public function isValid($value): bool
            {
                if (!is_int($value)) {
                    return false;
                }

                return ($value >= $this->reference);
            }
        };
    }

    /**
     * Is an URI
     *
     * @return RuleInterface
     */
    public static function uri()
    {
        return new class implements RuleInterface {
            /**
             * Check if a given value is an URI
             *
             * @param mixed $value Value to validate.
             *
             * @return bool True if value is valid, false otherwise.
             */
            public function isValid($value): bool
            {
                $result = filter_var($value, FILTER_VALIDATE_URL);
                return is_string($result) ? true : false;
            }
        };
    }

    /**
     * Verify if a value is in a set of given values.
     *
     * @param array $values Acceptable values.
     *
     * @return RuleInterface
     */
    public static function in(array $values)
    {
        return new class($values) implements RuleInterface {
            /**
             * @var array
             */
            protected $validValues = [];

            /**
             * InRule constructor.
             *
             * @param array $values Acceptable values.
             */
            public function __construct(array $values)
            {
                $this->validValues = $values;
            }

            /**
             * Check if a given value is one of acceptable values.
             *
             * @param mixed $value Value to validate.
             *
             * @return bool True if value is valid, false otherwise.
             */
            public function isValid($value): bool
            {
                return in_array($value, $this->validValues);
            }
        };
    }

    /**
     * Validate through a callback
     *
     * @param callable $callback Callback with validation logic.
     *
     * @return RuleInterface
     */
    public static function callbackValidation(callable $callback)
    {
        return new class($callback) implements RuleInterface {
            /**
             * @var callable
             */
            protected $callback;

            /**
             * Callback validation constructor.
             *
             * @param callable $callback Callback with validation logic.
             */
            public function __construct(callable $callback)
            {
                $this->callback = $callback;
            }

            /**
             * Check if a given value is valid.
             *
             * @param mixed $value Value to validate.
             *
             * @return bool True if value is valid, false otherwise.
             */
            public function isValid($value): bool
            {
                $fn = $this->callback;
                return $fn($value);
            }
        };
    }
}
