<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Rules;

/**
 * Provides validation rules for inputs data
 *
 * @package ArangoDB\Validation
 * @author Lucas S. Vieira
 */
abstract class Rules
{
    /**
     * Is array ?
     *
     * @return RuleInterface
     */
    public static function arr()
    {
        return new class implements RuleInterface {
            /**
             * Check if a given value is valid
             *
             * @param $value
             * @return bool
             */
            public function isValid($value): bool
            {
                return is_array($value);
            }
        };
    }

    /**
     * Is string ?
     *
     * @return RuleInterface
     */
    public static function string()
    {
        return new class implements RuleInterface {
            /**
             * Check if a given value is valid
             *
             * @param $value
             * @return bool
             */
            public function isValid($value): bool
            {
                return is_string($value);
            }
        };
    }

    /**
     * Is numeric ?
     *
     * @return RuleInterface
     */
    public static function numeric()
    {
        return new class implements RuleInterface {
            /**
             * Check if a given value is valid
             *
             * @param $value
             * @return bool
             */
            public function isValid($value): bool
            {
                return is_numeric($value);
            }
        };
    }

    /**
     * Is integer ?
     *
     * @return RuleInterface
     */
    public static function integer()
    {
        return new class implements RuleInterface {
            /**
             * Check if a given value is valid
             *
             * @param $value
             * @return bool
             */
            public function isValid($value): bool
            {
                return is_integer($value);
            }
        };
    }

    /**
     * Is boolean ?
     *
     * @return RuleInterface
     */
    public static function boolean()
    {
        return new class implements RuleInterface {
            /**
             * Check if a given value is valid
             *
             * @param $value
             * @return bool
             */
            public function isValid($value): bool
            {
                return is_bool($value);
            }
        };
    }

    /**
     * Is a primitive type ?
     *
     * @return RuleInterface
     */
    public static function isPrimitive()
    {
        return new class implements RuleInterface {
            /**
             * Check if a given value is valid
             *
             * @param $value
             * @return bool
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
     * If a value is equals to or greater than some given value
     *
     * @param int $reference
     * @return RuleInterface
     */
    public static function equalsOrGreaterThan(int $reference)
    {
        return new class($reference) implements RuleInterface {
            /**
             * Reference value
             *
             * @var integer
             */
            protected $reference = 0;

            /**
             * equalsOrGreaterThan Rule constructor.
             *
             * @param int $reference
             */
            public function __construct(int $reference)
            {
                $this->reference = $reference;
            }

            /**
             * Check if a given value is valid
             *
             * @param $value
             * @return bool
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
     * Is an uri ?
     *
     * @return RuleInterface
     */
    public static function uri()
    {
        return new class implements RuleInterface {
            /**
             * Check if a given value is valid
             *
             * @param $value
             * @return bool
             */
            public function isValid($value): bool
            {
                $result = filter_var($value, FILTER_VALIDATE_URL);
                return is_string($result) ? true : false;
            }
        };
    }

    /**
     * In - Rule
     *
     * @param array $values
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
             * @param array $values
             */
            public function __construct(array $values)
            {
                $this->validValues = $values;
            }

            /**
             * Check if a given value is valid
             *
             * @param $value
             * @return bool
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
     * @param callable $callback
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
             * @param callable $callback
             */
            public function __construct(callable $callback)
            {
                $this->callback = $callback;
            }

            /**
             * Check if a given value is valid
             *
             * @param $value
             * @return bool
             */
            public function isValid($value): bool
            {
                $fn = $this->callback;
                return $fn($value);
            }
        };
    }
}
