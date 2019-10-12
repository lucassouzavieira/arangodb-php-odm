<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Rules;

/**
 * Class Rules
 *
 * @package ArangoDB\Validation
 * @copyright 2019 Lucas S. Vieira
 */
abstract class Rules
{
    /**
     * Is array ?
     * @return Base
     */
    public static function arr()
    {
        return new class extends Base
        {
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
     * @return Base
     */
    public static function string()
    {
        return new class extends Base
        {
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
     * @return Base
     */
    public static function numeric()
    {
        return new class extends Base
        {
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
     * @return Base
     */
    public static function integer()
    {

        return new class extends Base
        {
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
     * @return Base
     */
    public static function boolean()
    {
        return new class extends Base
        {
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
     * Is an uri ?
     * @return Base
     */
    public static function uri()
    {
        return new class extends Base
        {
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
     * @param array $values
     * @return Base
     */
    public static function in(array $values)
    {
        return new class($values) extends Base
        {
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
}
