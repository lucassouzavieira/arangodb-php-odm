<?php


namespace ArangoDB\Validation\Rules;

/**
 * Class Rule
 *
 * @package ArangoDB\Validation\Rules
 * @copyright 2019 Lucas S. Vieira
 */
abstract class Base
{
    /**
     * Check if a given value is valid
     *
     * @param $value
     * @return bool
     */
    abstract public function isValid($value): bool;
}
