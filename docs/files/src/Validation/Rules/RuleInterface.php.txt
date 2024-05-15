<?php
declare(strict_types=1);

namespace ArangoDB\Validation\Rules;

/**
 * Basic interface for validation rule.
 *
 * @package ArangoDB\Validation\Rules
 * @author Lucas S. Vieira
 */
interface RuleInterface
{
    /**
     * Check if a given value is valid.
     *
     * @param mixed $value Value to validate.
     *
     * @return bool True if value is valid, false otherwise.
     */
    public function isValid($value): bool;
}
