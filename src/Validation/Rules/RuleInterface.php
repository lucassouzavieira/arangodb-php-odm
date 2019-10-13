<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Rules;

/**
 * Basic inter
 *
 * @package ArangoDB\Validation\Rules
 * @copyright 2019 Lucas S. Vieira
 */
interface RuleInterface
{
    /**
     * Check if a given value is valid
     *
     * @param $value
     * @return bool
     */
    public function isValid($value): bool;
}
